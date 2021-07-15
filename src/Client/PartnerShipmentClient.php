<?php

/**
 * PartnerShipmentClient File Doc Comment
 * php version 7.4.15
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market/06_Shipments/v1/shipment-interface.html
 */

namespace Otto\Market\Client;

use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Otto\Market\Shipments\ObjectSerializer;
use Otto\Market\Shipments\Model\Link;
use Otto\Market\Shipments\Model\Shipment;
use Otto\Market\Shipments\Model\ShipmentList;
use Otto\Market\Shipments\Model\ShipmentWithMinimumDetails;
use Otto\Market\Shipments\Model\CreateShipmentRequest;
use Otto\Market\Shipments\Model\CreateShipmentResponse;
use Otto\Market\Shipments\Model\PositionItem;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * PartnerShipmentClient class is a PHP client for the OTTO Market Shipment API.
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.nonlive.api.otto.market/06_Shipments/v1/shipment-interface.html
 *
 */
class PartnerShipmentClient
{
    private const API_VERSION      = "/v1";
    private const SHIPMENTS_PATH   = "shipments";
    private const CARRIERS         = "carriers";
    private const TRACKING_NUMBERS = "trackingnumbers";
    private const POSITION_ITEMS   = "positionitems";
    private const APPLICATION_JSON = "application/json";

    /** @var Configuration The client configuration. */
    private Configuration $configuration;

    /** @var LoggerInterface The logger to use. */
    private LoggerInterface $logger;

    /** @var Oauth2ApiAccessor The secured resources accessor. */
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
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Oauth2ApiAccessor $accessor
    ) {
        $logger->debug("Creating new client for partner shipments");
        $this->configuration = $configuration;
        $this->logger        = $logger;
        $this->accessor      = $accessor;
    }

    /**
     * Load all shipments created from the specified date onwards
     *
     * @param string $dateFrom an ISO 8601 formatted datetime (UTC)
     *
     * @return ShipmentWithMinimumDetails[] the shipments - may be an empty array.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     */
    public function getShipments(string $dateFrom): array
    {
        $data   = [
            'datefrom' => $dateFrom,
            'limit'    => 25,
        ];
        $params = http_build_query($data);
        $href   = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH . "?" . $params,
            ]
        );
        $result = [];
        do {
            $shipmentList = $this->getShipmentList($href);
            $href = null;
            if (!is_null($shipmentList)) {
                $result       = array_merge($result, (array) $shipmentList->getResources());
                $href         = $this->getLink($shipmentList->getLinks(), 'next');
            }
        } while ($href !== null);

        return $result;
    }

    /**
     * Load a shipment by its ID value.
     *
     * @param string $shipmentId the ID value of the shipment.
     *
     * @return Shipment the shipment or null if none exists for the specified ID.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     */
    public function getShipmentById(string $shipmentId): ?Shipment
    {
        $url = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH,
                $shipmentId,
            ]
        );
        return $this->getOneShipment($url);
    }

    /**
     * Load a shipment by its carrier and tracking number.
     *
     * @param string $carrier the carrier of the shipment. Available values
     *                               are:
     *                               DHL, DHL_FREIGHT, DHL_HOME_DELIVERY, GLS,
     *                               HERMES, DPD, UPS, HES, HELLMANN, DB_SCHENKER,
     *                               IDS, EMONS, DACHSER, LOGWIN, KUEHNE_NAGEL,
     *                               SCHOCKEMOEHLE, KOCH, REITHMEIER, OTHER_FORWARDER
     * @param string $trackingNumber the carriers tracking number of the shipment.
     *
     * @return Shipment the shipment or null if none exists for the specified ID.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     */
    public function getShipmentByCarrierAndTrackingNumber(
        string $carrier,
        string $trackingNumber
    ): ?Shipment {
        $url = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH,
                self::CARRIERS,
                $carrier,
                self::TRACKING_NUMBERS,
                $trackingNumber,
            ]
        );
        return $this->getOneShipment($url);
    }

    /**
     * Load the shipment resource that can be found under the specified URL.
     *
     * @param string $url the resource URL.
     *
     * @return Shipment|null the shipment or null if none exists for the specified ID.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    private function getOneShipment(string $url): ?Shipment
    {
            $response = $this->accessor->get($url);
            return ObjectSerializer::deserialize(
                $response->getBody()->getContents(),
                '\Otto\Market\Shipments\Model\Shipment'
            );
    }

    /**
     * Searches for the first link with the specified relation tag and returns
     * the href of this link. If no link could be found null is returned.
     *
     * @param Link[]|null $links the array of links that should be searched.
     * @param string $rel   the relation tag that is sought.
     *
     * @return string the first links href value or null.
     */
    private function getLink(?array $links, string $rel): ?string
    {
        if ($links !== null) {
            $nextLink = array_filter(
                $links,
                function ($e) use (&$rel) {
                    return $e->getRel() == $rel;
                }
            );
            if (empty($nextLink) === false) {
                return $nextLink[0]->getHref();
            }
        }

        return null;
    }

    /**
     * Loads the shipment list from the specified url.
     *
     * @param string $href the URL.
     *
     * @return ShipmentList|null
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    private function getShipmentList($href): ?ShipmentList
    {
        $response = $this->accessor->get($href);
        return ObjectSerializer::deserialize(
            $response->getBody()->getContents(),
            '\Otto\Market\Shipments\Model\ShipmentList'
        );
    }

    /**
     * Creates a shipment with a list of position items. It confirms that the
     * position items in the list have been handed over to the carrier for
     * final delivery to the customer. At this point, the position items are
     * marked with the state ''SENT'' in OTTO Market. This is the trigger for
     * the generation of a purchase receipt.
     *
     * @param CreateShipmentRequest $shipment the shipment
     *
     * @return CreateShipmentResponse|null the ID of the newly created shipment or
     *                                null if none exists something went wrong.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function createShipment(CreateShipmentRequest $shipment): ?CreateShipmentResponse
    {
        $url          = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH,
            ]
        );
        $shipmentJson = json_encode($shipment);
        $response     = $this->accessor->post(
            $url,
            $shipmentJson,
            ['Content-Type' => self::APPLICATION_JSON]
        );
        return ObjectSerializer::deserialize(
            $response->getBody()->getContents(),
            '\Otto\Market\Shipments\Model\CreateShipmentResponse'
        );
    }

    /**
     * Creates a shipment with a list of position items. It confirms that the
     * position items in the list have been handed over to the carrier for
     * final delivery to the customer. At this point, the position items are
     * marked with the state ''SENT'' in OTTO Market. This is the trigger for
     * the generation of a purchase receipt.
     *
     * @param string $shipmentJson the shipment as JSON string.
     *
     * @return CreateShipmentResponse|null the ID of the newly created shipment or
     *                                null if none exists something went wrong.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function createShipmentFromJson(string $shipmentJson): ?CreateShipmentResponse
    {
        $url      = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH,
            ]
        );
        $response = $this->accessor->post(
            $url,
            $shipmentJson,
            ['Content-Type' => self::APPLICATION_JSON]
        );
        return ObjectSerializer::deserialize(
            $response->getBody()->getContents(),
            '\Otto\Market\Shipments\Model\CreateShipmentResponse'
        );
    }

    /**
     * Adds a position item to an existing shipment. Note that this is
     * just a correction process for shipments where position items are
     * missing. A separate purchase receipt will be generated for the
     * newly added position items.
     *
     * @param string $shipmentId the ID value of the shipment.
     * @param PositionItem[] $positionItems the position item to add.
     *
     * @return int the number of position items successfully added.
     * @throws ClientExceptionInterface
     * @throws Oauth2\Oauth2Exception
     */
    public function addPositionItemsToShipment(
        string $shipmentId,
        array $positionItems
    ): int {
        $url = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH,
                $shipmentId,
                self::POSITION_ITEMS,
            ]
        );
        $positionItemsJson = json_encode($positionItems);
        $this->accessor->post(
            $url,
            $positionItemsJson,
            ['Content-Type' => self::APPLICATION_JSON]
        );
        return count($positionItems);
    }

    /**
     * Adds a position item to an existing shipment. Note that this is
     * just a correction process for shipments where position items are
     * missing. A separate purchase receipt will be generated for the
     * newly added position items.
     *
     * @param string $carrier the carrier of the shipment. Available
     *                                       values are: DHL, DHL_FREIGHT,
     *                                       DHL_HOME_DELIVERY, GLS,  HERMES, DPD,
     *                                       UPS, HES, HELLMANN, DB_SCHENKER, IDS,
     *                                       EMONS, DACHSER, LOGWIN, KUEHNE_NAGEL,
     *                                       SCHOCKEMOEHLE, KOCH, REITHMEIER,
     *                                       OTHER_FORWARDER
     * @param string $trackingNumber the carriers tracking number of the
     *                                       shipment.
     * @param PositionItem[] $positionItems the position item to add.
     *
     * @return int
     * @throws Oauth2\Oauth2Exception
     * @throws ClientExceptionInterface
     */
    public function addPositionItemsToShipmentByCarrierAndTrackingNumber(
        string $carrier,
        string $trackingNumber,
        array $positionItems
    ): int {
        $url = implode(
            "/",
            [
                self::API_VERSION,
                self::SHIPMENTS_PATH,
                self::CARRIERS,
                $carrier,
                self::TRACKING_NUMBERS,
                $trackingNumber,
                self::POSITION_ITEMS,
            ]
        );
        $positionItemsJson = json_encode($positionItems);
        $this->accessor->post(
            $url,
            $positionItemsJson,
            ['Content-Type' => self::APPLICATION_JSON]
        );
        return count($positionItems);
    }
}
