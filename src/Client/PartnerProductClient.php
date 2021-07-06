<?php

/**
 * PartnerProductClient File Doc Comment
 * php version 7.4.15
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market/03_Products/v1/products-interface.html
 */

namespace Otto\Market\Client;

use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Otto\Market\Products\Model\ActiveStatus;
use Otto\Market\Products\Model\ActiveStatusListRequest;
use Otto\Market\Products\Model\ProductProcessProgress;
use Otto\Market\Products\Model\ProductProcessResultLink;
use Otto\Market\Products\Model\CategoryGroup;
use Otto\Market\Products\ObjectSerializer;
use Otto\Market\Products\Model\Brand;
use Otto\Market\Products\Model\ProductVariation;
use Otto\Market\Products\Model\MarketPlaceStatus;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * PartnerProductClient class is a PHP client for the OTTO Market Products API.
 * See https://public-docs.live.api.otto.market/03_Products/v1/products-interface.html
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market/03_Products/v1/products-interface.html
 */
class PartnerProductClient
{
    private const API_VERSION   = "/v1";
    private const PRODUCTS_PATH = "products";

    /**
    * the maximum amount of product variants that can be sent in one POST request
    */
    private const MAX_PRODUCT_POST_SIZE = 500;

    /**
     * The client configuration.
     *
     * @var Configuration The configuration object.
     */
    private Configuration $configuration;

    /**
     * The logger to use.
     *
     * @var LoggerInterface The logger to use.
     */
    private LoggerInterface $logger;

    /**
     * The secured resources accessor.
     *
     * @var Oauth2ApiAccessor The accessor to use.
     */
    private Oauth2ApiAccessor $accessor;

    /**
     * Create a new client.
     *
     * @param Configuration     $configuration the client configuration.
     * @param LoggerInterface   $logger        the logger that should be used
     *                                         by the client.
     * @param Oauth2ApiAccessor $accessor      needed to access the secured
     *                                         resources.
     */
    public function __construct(Configuration $configuration, LoggerInterface $logger, Oauth2ApiAccessor $accessor)
    {
        $logger->debug("Creating new client for partner products");
        $this->configuration = $configuration;
        $this->logger        = $logger;
        $this->accessor      = $accessor;
    }

    /**
     * Load all brands
     *
     * @return Brand[]|null an array of all brands.
     * @throws Oauth2\Oauth2Exception on token refresh exception
     * @throws ClientExceptionInterface on HTTP client exception
     */
    public function getBrands(): ?array
    {
        $response = $this->accessor->get(implode("/", [self::API_VERSION, self::PRODUCTS_PATH, 'brands']));
        $this->logger->debug('Response with status code ' . $response->getStatusCode());
        return ObjectSerializer::deserialize(
            $response->getBody()->getContents(),
            '\Otto\Market\Products\Model\Brand[]'
        );
    }

    /**
     * Load all categories
     *
     * @return CategoryGroup[] an array of all brands.
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress PossiblyInvalidMethodCall
     */
    public function getCategories(): array
    {
        $mergedCategoryGroups = [];
        $nextLink = implode("/", [self::API_VERSION, self::PRODUCTS_PATH, 'categories']);
        do {
            $response = $this->accessor->get($nextLink);
            /*
             * @var CategoryGroups $categoryGroups
             */
            $categoryGroups       = ObjectSerializer::deserialize(
                $response->getBody()->getContents(),
                '\Otto\Market\Products\Model\CategoryGroups'
            );
            $nextLink = null;
            if (!is_null($categoryGroups)) {
                $mergedCategoryGroups = array_merge($mergedCategoryGroups, $categoryGroups->getCategoryGroups());

                foreach ($categoryGroups->getLinks() as $link) {
                    if ($link->getRel() == 'next') {
                        $nextLink = $link->getHref();
                        $this->logger->debug("Accessing next link " . $link->getHref());
                        break;
                    }
                }
            }
        } while (!is_null($nextLink));
        return $mergedCategoryGroups;
    }

    /**
     * Load a category definition by category name.
     *
     * @param  string $givenCategory category to search for
     *
     * @return CategoryGroup|null a CategoryGroup object if found, else null.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress PossiblyNullIterator
     */
    public function getCategoryDefinition(string $givenCategory): ?CategoryGroup
    {
        $categoryGroups = $this->getCategories();
        foreach ($categoryGroups as $categoryGroup) {
            foreach ($categoryGroup->getCategories() as $category) {
                if ($category == $givenCategory) {
                    /*
                    * @var CategoryGroup $categoryGroup
                    */
                    return $categoryGroup;
                }
            }
        }
        return null;
    }

    /**
     * Get all product from defined request
     *
     * @param  string|null $sku search parameter sku
     * @param  string|null $productName search parameter productName
     * @param  string|null $category search parameter category
     * @param  string|null $brand search parameter brand
     *
     * @return ProductVariation[] an array of all found products.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function getProducts(
        ?string $sku = null,
        ?string $productName = null,
        ?string $category = null,
        ?string $brand = null
    ): array {
        $nextLink = implode("/", [self::API_VERSION, self::PRODUCTS_PATH]);
        $nextLink = $this->buildProductsLinkFromParameter($nextLink, $sku, $productName, $category, $brand);

        $listOfProductVariations = [];

        while (!is_null($nextLink)) {
            $this->logger->debug($nextLink);
            $response = $this->accessor->get($nextLink);

            $data = json_decode($response->getBody()->getContents());

            foreach ($data->productVariations as $variation) {
                /*
                 * @var ProductVariation $productVariation
                 */
                $productVariation =
                    ObjectSerializer::deserialize($variation, "\Otto\Market\Products\Model\ProductVariation");
                array_push($listOfProductVariations, $productVariation);
            }

            $nextLink = null;
            foreach ($data->links as $link) {
                if ($link->rel == 'next') {
                    $nextLink = $link->href;
                    $this->logger->debug("Accessing next product link " . $link->href);
                }
            }
        }//end while

        return $listOfProductVariations;
    }

    /**
     * Posts all product variations.
     * This method sends multiple uploads, if the given array is larger than the maximum allowed number of
     * products that can be sent per request.
     *
     * @param  ProductVariation[] $products
     *
     * @return ProductProcessProgress[] an array of all initial progress results.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function postProducts(array $products): array
    {
        /*
         * @var string[] $initialProgresses
         */
        $initialProgresses = [];

        $path   = implode("/", [self::API_VERSION, self::PRODUCTS_PATH]);
        $offset = 0;
        while ($offset < sizeof($products)) {
            $nextPostArray = array_slice($products, $offset, self::MAX_PRODUCT_POST_SIZE);
            $this->logger->debug("Posting next batch of " . sizeof($nextPostArray) . " product variations");
            $serializedPayload =
                json_encode(
                    $nextPostArray,
                    (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS)
                );
            $postResult        =
                $this->accessor->post($path, $serializedPayload, ['Content-Type' => 'application/json']);
            $this->logger->debug("POST returned with status code " . $postResult->getStatusCode());
            /*
             * @var ProductProcessProgress $parsedPostResult
             */
            $parsedPostResult = ObjectSerializer::deserialize(
                $postResult->getBody()->getContents(),
                '\Otto\Market\Products\Model\ProductProcessProgress'
            );
            array_push($initialProgresses, $parsedPostResult);
            $offset = ($offset + self::MAX_PRODUCT_POST_SIZE);
        }

        return $initialProgresses;
    }

    /**
     * Refresh the progress of an upload, and return the updated progress.
     * The last progress result is used as a token to retrieve the new state.
     *
     * @param  ProductProcessProgress $lastProgress
     *
     * @return ProductProcessProgress the ProductProcessProgress result.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress PossiblyNullArgument
     * @psalm-suppress InvalidReturnStatement
     */
    public function getUpdatedUploadProgress(ProductProcessProgress $lastProgress): ?ProductProcessProgress
    {
        if ($lastProgress->getPingAfter()->getTimestamp() < time()) {
            $response = $this->accessor->get(self::getLink($lastProgress->getLinks(), 'self'));
            /*
             * @var ProductProcessProgress $updatedProgress
             */
            $updatedProgress = ObjectSerializer::deserialize(
                $response->getBody()->getContents(),
                '\Otto\Market\Products\Model\ProductProcessProgress'
            );
            $this->logger->debug("Updated upload progress");
            return $updatedProgress;
        } else {
            $this->logger->debug("Did not update upload progress - pingAfter not yet reached");
            return $lastProgress;
        }
    }

    /**
     * Get marketplace-status for a given sku
     *
     * @param  string $sku
     *
     * @return MarketPlaceStatus|null the marketPlaceStatus of the given sku.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function getMarketplaceStatus(string $sku): ?MarketPlaceStatus
    {
        $this->logger->debug("getMarketplaceStatus called for sku=$sku");
        $response =
            $this->accessor->get(implode("/", [self::API_VERSION, self::PRODUCTS_PATH, $sku, 'marketplace-status']));
        /*
         * @var MarketPlaceStatus $marketplaceStatus
         */
        $marketplaceStatus = ObjectSerializer::deserialize(
            $response->getBody()->getContents(),
            '\Otto\Market\Products\Model\MarketPlaceStatus'
        );
        return $marketplaceStatus;
    }

    /**
     * Get active-status for a given sku
     *
     * @param  string $sku search parameter sku
     *
     * @return ActiveStatus|null the activeStatus of the given sku.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function getActiveStatus(string $sku): ?ActiveStatus
    {
        $this->logger->debug("getActiveStatus called for sku=$sku");
        $response = $this->accessor->get(implode("/", [self::API_VERSION, self::PRODUCTS_PATH, $sku, 'active-status']));
        /*
         * @var ActiveStatus $activeStatus
         */
        $activeStatus = ObjectSerializer::deserialize(
            $response->getBody()->getContents(),
            '\Otto\Market\Products\Model\ActiveStatus'
        );
        return $activeStatus;
    }

    /**
     * Update active-status for a list of skus
     *
     * @param  ActiveStatusListRequest $activeStatusListRequest
     *
     * @return ProductProcessProgress the ProductProcessProgress result.
     *
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2\Oauth2Exception on token refresh exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function postActiveStatus(ActiveStatusListRequest $activeStatusListRequest): ?ProductProcessProgress
    {
        $this->logger->debug("postActiveStatus called for ActiveStatusListRequest=$activeStatusListRequest");
        $path = implode("/", [self::API_VERSION, self::PRODUCTS_PATH, 'active-status']);
        $serializedPayload = json_encode(
            $activeStatusListRequest,
            (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS)
        );
        $postResult        = $this->accessor->post($path, $serializedPayload, ['Content-Type' => 'application/json']);
        $this->logger->debug("POST returned with status code " . $postResult->getStatusCode());
        /*
         * @var ProductProcessProgress $parsedPostResult
         */
        $parsedPostResult = ObjectSerializer::deserialize(
            $postResult->getBody()->getContents(),
            '\Otto\Market\Products\Model\ProductProcessProgress'
        );
        return $parsedPostResult;
    }

    /**
     * Builds all needed parameters to working http path
     *
     * @param  string      $nextLink
     * @param  string|null $sku
     * @param  string|null $productName
     * @param  string|null $category
     * @param  string|null $brand
     * @return string
     */
    private function buildProductsLinkFromParameter(
        string $nextLink,
        ?string $sku,
        ?string $productName,
        ?string $category,
        ?string $brand
    ): string {
        if (!empty($sku)) {
            if (strpos($nextLink, '?') !== false) {
                $nextLink = $nextLink . '&sku=' . $sku;
            } else {
                $nextLink = $nextLink . '?sku=' . $sku;
            }
        }

        if (!empty($productName)) {
            if (strpos($nextLink, '?') !== false) {
                $nextLink = $nextLink . '&productName=' . $productName;
            } else {
                $nextLink = $nextLink . '?productName=' . $productName;
            }
        }

        if (!empty($category)) {
            if (strpos($nextLink, '?') !== false) {
                $nextLink = $nextLink . '&category=' . $category;
            } else {
                $nextLink = $nextLink . '?category=' . $category;
            }
        }

        if (!empty($brand)) {
            if (strpos($nextLink, '?') !== false) {
                $nextLink = $nextLink . '&brand=' . $brand;
            } else {
                $nextLink = $nextLink . '?brand=' . $brand;
            }
        }

        return $nextLink;
    }

    /**
     * Searches for the first link with the specified relation tag and returns
     * the href of this link. If no link could be found null is returned.
     *
     * @param ProductProcessResultLink[] $links the array of links that should be searched.
     * @param string                     $rel   the relation tag that is sought.
     *
     * @return string the first links href value or null.
     */
    private static function getLink(array $links, string $rel): ?string
    {
        if ($links != null) {
            $nextLink = array_filter(
                $links,
                function ($e) use (&$rel) {
                    return $e->getRel() == $rel;
                }
            );
            if (!empty($nextLink) > 0) {
                return $nextLink[0]->getHref();
            }
        }

        return null;
    }
}
