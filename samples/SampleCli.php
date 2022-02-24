<?php

namespace Otto\Market\Cli\Client;

use Otto\Market\Client\Configuration;
use Otto\Market\Client\Oauth2\Oauth2Exception;
use Otto\Market\Client\PartnerApiClient;
use Otto\Market\Client\PartnerProductClient;
use Otto\Market\Client\PartnerShipmentClient;
use Otto\Market\Products\Model\ProductProcessProgress;
use Otto\Market\Products\Model\ActiveStatusListRequest;
use Otto\Market\Products\ObjectSerializer;
use splitbrain\phpcli\Options;
use splitbrain\phpcli\PSR3CLI;

require_once __DIR__.'/../vendor/autoload.php';

class SampleCli extends PSR3CLI
{

    private PartnerApiClient $partnerApiClient;

    protected function setup(Options $options)
    {
        $options->setHelp('An awesome CLI for accessing the Otto Market API');

        $options->registerOption('help', 'print help', 'h');
        $options->registerOption('version', 'print version', 'v');
        $options->registerOption('user', 'username for authentication', 'u', 'user');
        $options->registerOption('password', 'password for authentication', 'p', 'password');

        $options->registerOption('environment', 'environment: live or sandbox', 'e', 'environment');

        $options->registerCommand('product', 'list all products');
        $options->registerOption('sku', 'filter for specific sku', 's', 'sku', 'product');
        $options->registerOption('productReference', 'filter for specific productReference', 'p', 'productReference', 'product');
        $options->registerOption('category', 'filter for specific category', 'c', 'category', 'product');
        $options->registerOption('brand', 'filter for specific brand', 'b', 'brand', 'product');
        $options->registerOption('format', 'determine output format, e.g. json', 'f', 'format', 'product');

        $options->registerCommand('uploadProduct', 'upload product data to otto market api');
        $options->registerArgument('postData', 'to upload product data from json file', true, 'uploadProduct');

        $options->registerCommand('categories', 'list categories');

        $options->registerCommand('category', 'inspect category');
        $options->registerArgument('categoryName', 'Name of the category you want to inspect.', true, 'category');

        $options->registerCommand('brands', 'list brands');

        $options->registerCommand('marketplace-status', 'get marketplace-status for a given sku');
        $options->registerArgument('sku', 'sku for which you want to get the status', true, 'marketplace-status');

        $options->registerCommand('active-status', 'get active-status for a given sku');
        $options->registerArgument('sku', 'sku for which you want to get the status', true, 'active-status');

        $options->registerCommand('upload-active-status', 'upload market-place-status data to otto market api');
        $options->registerArgument('postData', 'to upload market-place-status data from json file', true, 'upload-active-status');

        $options->registerCommand('shipments', 'list shipments');
        $options->registerArgument('dateFrom', 'Shipments created from this date onwards for the given authorized partner will be returned. The date is considered as ISO 8601 and UTC.', true, 'shipments');

        $options->registerCommand('shipmentById', 'get a shipment by its ID value');
        $options->registerArgument('id', 'the ID value to search for', true, 'shipmentById');
    }

    protected function main(Options $options)
    {
        switch ($options->getOpt('environment')) {
            case 'live':
                $configuration = Configuration::forLive($options->getOpt('user'), $options->getOpt('password'));
                break;
            case 'sandbox':
                $configuration = Configuration::forSandbox($options->getOpt('user'), $options->getOpt('password'));
                break;
            default:
                $this->error("Invalid environment");
                exit(1);
        }
        $this->partnerApiClient = new PartnerApiClient($configuration, $this);

        switch ($options->getCmd()) {
            case 'version':
                $this->info('0.0.0.0.0.1');
                break;
            case 'product':
                // @var ProductVariation[] $products
                $products = $this->productClient($options)->getProducts(
                    $options->getOpt('sku'),
                    $options->getOpt('productReference'),
                    $options->getOpt('category'),
                    $options->getOpt('brand')
                );
                if ($options->getOpt('format') == 'json') {
                    echo json_encode($products);
                } else {
                    foreach ($products as $product) {
                        echo 'ProductReference: ' . $product->getProductReference() . ' -> ' . 'sku: ' . $product->getSku() . "\n";
                    }
                }
                break;
            case 'uploadProduct':
                $fileContents = file_get_contents($options->getArgs()[0]);
                $productVariations = ObjectSerializer::deserialize($fileContents, '\Otto\Market\Products\Model\ProductVariation[]');
                $this->debug('Parsed ' . sizeof($productVariations) . ' product variations');
                $lastStatus = $this->productClient()->postProducts($productVariations);
                $this->info('Uploaded product variations.');
                while (true) {
                    foreach ($lastStatus as $status) {
                        $status = $this->productClient()->getUpdatedUploadProgress($status);
                        $this->info($status);
                    }
                    sleep(1);
                }
                break;
            case 'categories':
                // @var CategoryGroup[] $categories
                $it = $this->productClient()->getCategories();
                foreach ($it as $categoryGroup) {
                    foreach ($categoryGroup->getCategories() as $category) {
                        echo $categoryGroup->getCategoryGroup() . '->' . $category . "\n";
                    }
                }
                break;
            case 'category':
                // @var CategoryGroup $categoryDefinition
                $categoryDefinition = $this->productClient()->getCategoryDefinition($options->getArgs()[0]);
                echo $categoryDefinition;
                break;
            case 'brands':
                $brands = $this->productClient()->getBrands();
                foreach ($brands as $brand) {
                    echo $brand->getName() . "\n";
                }
                break;
            case 'marketplace-status':
                $marketplaceStatus = $this->productClient()->getMarketplaceStatus($options->getArgs()[0]);
                echo $marketplaceStatus->getStatus()."\n";
                break;
            case 'active-status':
                $activeStatus = $this->productClient()->getActiveStatus($options->getArgs()[0]);
                echo $activeStatus->getActive() ? 'true'."\n" : 'false'."\n";
                break;
            case 'upload-active-status':
                $fileContents = file_get_contents($options->getArgs()[0]);
                /* @var $activeStatusListRequest ActiveStatusListRequest */
                $activeStatusListRequest = ObjectSerializer::deserialize($fileContents, '\Otto\Market\Products\Model\ActiveStatusListRequest');
                $productProcessProgress = $this->productClient()->postActiveStatus($activeStatusListRequest);
                $this->info($productProcessProgress);
                $productProcessProgress = $this->waitUntilUploadNotPending($productProcessProgress);
                $this->info($productProcessProgress);
                break;
            case 'shipments':
                $shipments = $this->shipmentClient($options)->getShipments($options->getArgs()[0]);
                foreach ($shipments as $shipment) {
                    echo "ShipmentId:" . $shipment->getShipmentId() . "\n";
                }
                break;
            case 'shipmentById':
                $shipment = $this->shipmentClient($options)->getShipmentById($options->getArgs()[0]);
                print_r($shipment);
                break;
            default:
                echo $options->help();
        }
    }

    private function productClient(): PartnerProductClient
    {
        return $this->partnerApiClient->getPartnerProductClient();
    }

    private function shipmentClient(): PartnerShipmentClient
    {
        return $this->partnerApiClient->getPartnerShipmentClient();
    }

    /**
     * Gets the state and pingAfter attributes from the ProductProcessProgress and polls for progress until
     * state is not "pending"
     * @param ProductProcessProgress $productProcessProgress
     * @return ProductProcessProgress
     * @throws Oauth2Exception
     */
    private function waitUntilUploadNotPending(ProductProcessProgress $productProcessProgress): ProductProcessProgress
    {
        while ($productProcessProgress->getState() === "pending") {
            $pingAfter = $productProcessProgress->getPingAfter();
            $pingAfterString = $pingAfter->format('Y-m-d H:i:s');
            $waitTimeSeconds = $pingAfter->getTimestamp() - time();
            $this->info("pingAfter is $pingAfterString -> patiently waiting for $waitTimeSeconds seconds before updating ProductProcessStatus");
            sleep($waitTimeSeconds + 1);
            $productProcessProgress = $this->productClient()->getUpdatedUploadProgress($productProcessProgress);
        }
        return $productProcessProgress;
    }
}

$cli = new SampleCli();
$cli->run();
