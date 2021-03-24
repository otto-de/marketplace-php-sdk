<?php

declare(strict_types=1);

namespace Otto\Market\Test\Client;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use Otto\Market\Client\PartnerShipmentClient;
use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Otto\Market\Client\Configuration;
use Otto\Market\Shipments\Model\CreateShipmentRequest;
use Otto\Market\Shipments\Model\PositionItem;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once 'ShipmentJsonResponses.php';

final class PartnerShipmentClientTest extends TestCase
{

    private $stub;

    private $client;

    /**
     * Setup before running each test case
     */
    public function setUp(): void
    {
        $this->stub    = $this->createStub(Oauth2ApiAccessor::class);
        $configuration = Configuration::forNonlive("user", "password");
        $logger        = new Logger('name');
        $this->client  = new PartnerShipmentClient($configuration, $logger, $this->stub);
    }

    public function testGetShipmentsSimpleGoodPath(): void
    {
        $json     = JsonResponses::GET_SHIPMENTS;
        $response = new Response(200, [], $json);
        $this->stub->method('get')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments?datefrom=2020-09-03&limit=25',
                        [],
                        $response,
                    ],
                ]
            )
        );

        $result = $this->client->getShipments('2020-09-03');
        $this->assertEquals(2, count($result));
        $this->assertEquals("101074901541", $result[0]->getShipmentId());
        $this->assertEquals("101074897022", $result[1]->getShipmentId());
    }

    public function testGetShipmentsPagedGoodPath(): void
    {
        $json1     = JsonResponses::GET_SHIPMENTS_PAGE1;
        $json2     = JsonResponses::GET_SHIPMENTS_PAGE2;
        $response1 = new Response(200, [], $json1);
        $response2 = new Response(200, [], $json2);
        $this->stub->method('get')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments?datefrom=2020-09-02&limit=25',
                        [],
                        $response1,
                    ],
                    [
                        '/v1/shipments?next=101074897022&datefrom=2020-09-01&limit=1',
                        [],
                        $response2,
                    ],
                ]
            )
        );
        $result = $this->client->getShipments('2020-09-02');
        $this->assertEquals(2, count($result));
        $this->assertEquals("101074901541", $result[0]->getShipmentId());
        $this->assertEquals("101074897022", $result[1]->getShipmentId());
    }

    public function testGetShipmentsBadRequest(): void
    {
        $message   = JsonResponses::GET_SHIPMENTS_BAD_REQUEST;
        $request   = new Request("POST", "", []);
        $response  = new Response(400, [], $message);
        $exception = RequestException::create($request, $response);
        $this->stub->method('get')->will($this->throwException($exception));

        $this->expectException(ClientException::class);
        $this->client->getShipments('2020-09-02');
    }

    public function testGetShipmentByIdGoodPath(): void
    {
        $json     = JsonResponses::GET_SHIPMENT_BY_ID;
        $response = new Response(200, [], $json);
        $this->stub->method('get')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments/101074897022',
                        [],
                        $response,
                    ],
                ]
            )
        );

        $result = $this->client->getShipmentById('101074897022');
        $this->assertEquals("101074897022", $result->getShipmentId());
    }

    public function testGetShipmentByCarrierAndTrackingNumber(): void
    {
        $json     = JsonResponses::GET_SHIPMENT_BY_CARRIER;
        $response = new Response(200, [], $json);
        $this->stub->method('get')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments/carriers/HERMES/trackingnumbers/H5569853654983256987',
                        [],
                        $response,
                    ],
                ]
            )
        );

        $result = $this->client->getShipmentByCarrierAndTrackingNumber('HERMES', 'H5569853654983256987');
        $this->assertEquals("101074897022", $result->getShipmentId());
    }

    public function testCreateShipmentFromJson(): void
    {
        $json     = JsonResponses::CREATE_SHIPMENT;
        $response = new Response(200, [], $json);
        $this->stub->method('post')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments',
                        'JSON payload',
                        ['Content-Type' => 'application/json'],
                        $response,
                    ],
                ]
            )
        );

        $result = $this->client->createShipmentFromJson("JSON payload");
        $this->assertEquals("101074897022", $result->getShipmentId());
    }

    public function testCreateShipment(): void
    {
        $json     = JsonResponses::CREATE_SHIPMENT;
        $response = new Response(200, [], $json);
        $this->stub->method('post')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments',
                        '{"trackingKey":{"carrier":"HERMES","trackingNumber":"H5798872677608747081"}}',
                        ['Content-Type' => 'application/json'],
                        $response,
                    ],
                ]
            )
        );

        $request = new CreateShipmentRequest(
            [
                "trackingKey" => [
                    "carrier"        => "HERMES",
                    "trackingNumber" => "H5798872677608747081",
                ],
            ]
        );
        $result  = $this->client->createShipment($request);
        $this->assertEquals("101074897022", $result->getShipmentId());
    }

    public function testAddPositionItemsToShipment(): void
    {
        $json     = JsonResponses::CREATE_SHIPMENT;
        $response = new Response(200, [], $json);
        $this->stub->method('post')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments/4711/positionitems',
                        '[{"positionItemId":"1234","salesOrderId":"0815","returnTrackingKey":{"carrier":"HERMES","trackingNumber":"H5798872677608747081"}}]',
                        ['Content-Type' => 'application/json'],
                        $response,
                    ],
                ]
            )
        );

        $request = [new PositionItem(
            [
                "positionItemId"    => "1234",
                "salesOrderId"      => "0815",
                "returnTrackingKey" => [
                    "carrier"        => "HERMES",
                    "trackingNumber" => "H5798872677608747081",
                ],
            ]
        )
            ];
        $result  = $this->client->addPositionItemsToShipment("4711", $request);
        $this->assertEquals(1, $result);
    }

    public function testAddPositionItemsToShipmentByCarrierAndTrackingNumber(): void
    {
        $json     = JsonResponses::CREATE_SHIPMENT;
        $response = new Response(200, [], $json);
        $this->stub->method('post')->will(
            $this->returnValueMap(
                [
                    [
                        '/v1/shipments/carriers/HERMES/trackingnumbers/T4711/positionitems',
                        '[{"positionItemId":"1234","salesOrderId":"0815","returnTrackingKey":{"carrier":"HERMES","trackingNumber":"H5798872677608747081"}}]',
                        ['Content-Type' => 'application/json'],
                        $response,
                    ],
                ]
            )
        );

        $request = [new PositionItem(
            [
                "positionItemId"    => "1234",
                "salesOrderId"      => "0815",
                "returnTrackingKey" => [
                    "carrier"        => "HERMES",
                    "trackingNumber" => "H5798872677608747081",
                ],
            ]
        )
            ];
        $result  = $this->client->addPositionItemsToShipmentByCarrierAndTrackingNumber("HERMES", "T4711", $request);
        $this->assertEquals(1, $result);
    }
}
