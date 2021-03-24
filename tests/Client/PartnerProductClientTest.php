<?php declare(strict_types=1);

namespace Otto\Market\Test\Client;

use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Otto\Market\Client\Configuration;
use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Otto\Market\Client\PartnerProductClient;
use Otto\Market\Products\ObjectSerializer;
use PHPUnit\Framework\TestCase;

class PartnerProductClientTest extends TestCase
{
    private $stub;
    private $client;

    /**
     * Setup before running each test case
     */
    public function setUp(): void
    {
        $this->stub = $this->createStub(Oauth2ApiAccessor::class);
        $configuration = Configuration::forNonlive("user", "password");
        $logger = new Logger('name');
        $this->client = new PartnerProductClient($configuration, $logger, $this->stub);
    }

    public function testGetMarketplaceStatus(): void
    {
        $json = ProductsJsonResponses::GET_MARKETPLACESTATUS_ERROR;
        $response = new Response(200, array(), $json);

        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products/123363682/marketplace-status', array(), $response]]
            )
        );

        $result = $this->client->getMarketplaceStatus("123363682");
        $this->assertEquals("600004 - VARIATIONTHEME_INVALID", $result->getErrors()[0]->getCode());
        $this->assertEquals("REJECTED", $result->getStatus());
        $this->assertEquals("/v1/products/123363682", $result->getLinks()[0]->getHref());
        $this->assertEquals("variation", $result->getLinks()[0]->getRel());
    }

    public function testGetActiveStatus(): void
    {
        $json = ProductsJsonResponses::GET_ACTIVE_STATUS;
        $response = new Response(200, array(), $json);

        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products/123363682/active-status', array(), $response]]
            )
        );

        $result = $this->client->getActiveStatus("123363682");
        $this->assertEquals("123363682", $result->getSku());
        $this->assertEquals(true, $result->getActive());
        $this->assertEquals("2020-12-08T10:45:49.525Z", $result->getLastModified());
    }

    public function testPostActiveStatus() : void
    {
        $jsonResponseBody = ProductsJsonResponses::POST_ACTIVE_STATUS;
        $response = new Response(202, [], $jsonResponseBody);
        $jsonRequestBody = ProductsJsonRequests::POST_ACTIVE_STATUS;
        $activeStatusListRequest = ObjectSerializer::deserialize(
            $jsonRequestBody,
            '\Otto\Market\Products\Model\ActiveStatusListRequest'
        );

        $this->stub->method('post')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/products/active-status',
                        $jsonRequestBody,
                        ['Content-Type' => 'application/json'],
                        $response,
                    ],
                ]
            )
        );

        $result = $result = $this->client->postActiveStatus($activeStatusListRequest);
        $this->assertEquals("pending", $result->getState());
        $this->assertEquals(new \DateTime("2021-03-15T10:26:15.148+0000"), $result->getPingAfter());
        $this->assertEquals(0, $result->getProgress());
        $this->assertEquals(2, $result->getTotal());
        $this->assertEquals(3, count($result->getLinks()));
        $this->assertEquals("self", $result->getLinks()[0]->getRel());
        $this->assertEquals(
            "/v1/products/update-tasks/e2484daf-440f-4b1a-a6d1-f6a370a3d333",
            $result->getLinks()[0]->getHref()
        );
        $this->assertEquals("failed", $result->getLinks()[1]->getRel());
        $this->assertEquals(
            "/v1/products/update-tasks/e2484daf-440f-4b1a-a6d1-f6a370a3d333/failed",
            $result->getLinks()[1]->getHref()
        );
        $this->assertEquals("succeeded", $result->getLinks()[2]->getRel());
        $this->assertEquals(
            "/v1/products/update-tasks/e2484daf-440f-4b1a-a6d1-f6a370a3d333/succeeded",
            $result->getLinks()[2]->getHref()
        );
    }

    public function testGetCategories()
    {
        $json = ProductsJsonResponses::GET_CATEGORIES;
        $response = new Response(200, array(), $json);

        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products/categories', array(), $response]]
            )
        );

        $result = $this->client->getCategories();
        foreach ($result as $group) {
            $this->assertEquals(
                "$.productVariations[*].pricing.normPriceInfo",
                $group->getAdditionalRequirements()[0]->getJsonPath()
            );
        }
    }

    public function testGetSingleCategoryDefinition()
    {
        $json = ProductsJsonResponses::GET_CATEGORIES;
        $response = new Response(200, array(), $json);

        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products/categories', array(), $response]]
            )
        );

        $result = $this->client->getCategoryDefinition('Farbe');

        $this->assertInstanceOf('Otto\Market\Products\Model\CategoryGroup', $result);
    }

    public function testGetSingleCategoryDefinitionInvalid()
    {
        $json = ProductsJsonResponses::GET_CATEGORIES;
        $response = new Response(200, array(), $json);

        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products/categories', array(), $response]]
            )
        );

        $result = $this->client->getCategoryDefinition('InvalidCategory');

        $this->assertNull($result);
    }

    public function testGetProducts()
    {
        $json = ProductsJsonResponses::GET_PRODUCTS;
        $response = new Response(200, array(), $json);

        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products', array(), $response]]
            )
        );

        $result = $this->client->getProducts();

        $this->assertContainsOnlyInstancesOf('Otto\Market\Products\Model\ProductVariation', $result);
        $this->assertCount(2, $result);
    }

    public function testGetUpdatedUploadProgressChanged()
    {
        $json = ProductsJsonResponses::GET_PRODUCT_PROCESS_PROGRESS_UNCHANGED;
        $lastProgress = ObjectSerializer::deserialize(
            ProductsJsonResponses::GET_PRODUCT_PROCESS_PROGRESS_CHANGED,
            '\Otto\Market\Products\Model\ProductProcessProgress'
        );
        $response = new Response(200, array(), $json);
        
        $this->stub->method('get')->will(
            $this->returnValueMap(
                [['/v1/products', array(), $response]]
            )
        );

        $result = $this->client->getUpdatedUploadProgress($lastProgress);
        
        $this->assertEquals("9999-03-15 10:40:00", date_format($result->getPingAfter(), 'Y-m-d H:i:s'));
    }
        
    public function testGetUpdatedUploadProgressUnchanged()
    {
        $expectedProgress = ObjectSerializer::deserialize(
            ProductsJsonResponses::GET_PRODUCT_PROCESS_PROGRESS_UNCHANGED,
            '\Otto\Market\Products\Model\ProductProcessProgress'
        );

        $result = $this->client->getUpdatedUploadProgress($expectedProgress);

        $this->assertEquals($expectedProgress, $result);
    }
}
