<?php

declare(strict_types=1);

namespace Otto\Market\Test\Client;

use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Otto\Market\Client\Configuration;
use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Otto\Market\Client\PartnerProductClient;
use Otto\Market\Products\Model\ProductProcessProgress;
use Otto\Market\Products\Model\ProductVariation;
use Otto\Market\Products\ObjectSerializer;
use PHPUnit\Framework\TestCase;

class PartnerProductUploadTest extends TestCase
{
    private Oauth2ApiAccessor $stub;
    private PartnerProductClient $client;

    public function setUp(): void
    {
        $this->stub = $this->createStub(Oauth2ApiAccessor::class);
        $configuration = Configuration::forNonlive("user", "password");
        $logger = new Logger('name');
        $this->client = new PartnerProductClient($configuration, $logger, $this->stub);
    }

    public function testPostProduct(): void
    {
        $json = ProductUploadJson::PRODUCT_UPLOAD_RESPONSE;
        $response = new Response(202, array(), $json);
        $this->stub->method('post')->will(
            $this->returnValueMap(
                [['/v2/products',
                    ProductUploadJson::PRODUCT_PAYLOAD_ONE_PRODUCT,
                    ['Content-Type' => 'application/json'],
                    $response]]
            )
        );

        /** @var ProductVariation[] $products */
        $products = ObjectSerializer::deserialize(
            ProductUploadJson::PRODUCT_PAYLOAD_ONE_PRODUCT,
            'Otto\Market\Products\Model\ProductVariation[]'
        );
        $result = $this->client->postProducts($products);

        self::assertEquals(1, sizeof($result));
        self::assertEquals(1, $result[0]->getTotal());
        self::assertEquals(0, $result[0]->getProgress());
        self::assertEquals(date_create("2020-05-13T10:40:01.815+00:00"), $result[0]->getPingAfter());
        self::assertEquals("pending", $result[0]->getState());
        self::assertEquals("The process is currently in progress", $result[0]->getMessage());
        self::assertEquals("self", $result[0]->getLinks()[0]->getRel());
        self::assertEquals("failed", $result[0]->getLinks()[1]->getRel());
        self::assertEquals("succeeded", $result[0]->getLinks()[2]->getRel());
        self::assertEquals("unchanged", $result[0]->getLinks()[3]->getRel());
    }


}