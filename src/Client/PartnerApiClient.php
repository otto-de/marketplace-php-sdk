<?php

/**
 * PartnerApiClient File Doc Comment
 * php version 7.4.15
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 *
 */

namespace Otto\Market\Client;

use GuzzleHttp\Client;
use Monolog\Handler\NoopHandler;
use Monolog\Logger;
use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Psr\Log\LoggerInterface;

/**
 * PartnerApiClient is the overarching class that creates client for the
 * different OTTO Market APIs.
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 */
class PartnerApiClient
{

    private Oauth2ApiAccessor $accessor;

    private LoggerInterface $logger;

    private Configuration $configuration;

    private ?PartnerProductClient $partnerProductClient = null;

    private ?PartnerShipmentClient $partnerShipmentClient = null;

    /**
     * PartnerApiClient constructor.
     *
     * @param  Configuration        $configuration the configuration to apply to the client
     * @param  LoggerInterface|null $logger        the logger instance to write the logs to;
     *                                             if not provided, a noop-logger is used
     * @param  Client|null          $httpClient    optional HTTP client to use;
     *                                             if not provided, a default client is used
     * @throws Oauth2\Oauth2Exception if no valid initial token can be fetched
     */
    public function __construct(
        Configuration $configuration,
        ?LoggerInterface $logger = null,
        ?Client $httpClient = null
    ) {
        if (is_null($logger)) {
            $this->logger = new Logger('otto-api');
            $this->logger->pushHandler(new NoopHandler());
        } else {
            $this->logger = $logger;
        }

        $this->configuration = $configuration;

        if (is_null($httpClient)) {
            $this->logger->debug("No HTTP client provided for Oauth2ApiAccessor, constructing default Guzzle client");
            $httpClient = $this->createHttpClient($configuration);
        }

        $this->accessor = new Oauth2ApiAccessor($configuration, $this->logger, $httpClient);
    }

    /**
     * Get the sub-client for accessing product API.
     *
     * @return PartnerProductClient
     */
    public function getPartnerProductClient(): PartnerProductClient
    {
        if (is_null($this->partnerProductClient)) {
            $this->partnerProductClient =
                new PartnerProductClient($this->configuration, $this->logger, $this->accessor);
        }

        return $this->partnerProductClient;
    }

    /**
     * Get the sub-client for accessing the shipment API.
     *
     * @return PartnerShipmentClient
     */
    public function getPartnerShipmentClient(): PartnerShipmentClient
    {
        if (is_null($this->partnerShipmentClient)) {
            $this->partnerShipmentClient =
                new PartnerShipmentClient($this->configuration, $this->logger, $this->accessor);
        }

        return $this->partnerShipmentClient;
    }

    private function createHttpClient(Configuration $configuration): Client
    {
        return new Client(
            [
                'timeout' => $configuration->getHttpTimeout(),
            ]
        );
    }
}
