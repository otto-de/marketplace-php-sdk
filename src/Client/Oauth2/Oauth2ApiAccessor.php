<?php

/**
 * Oauth2ApiAccessor File Doc Comment
 * php version 7.4.15
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 *
 */

namespace Otto\Market\Client\Oauth2;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Otto\Market\Client\Configuration;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Oauth2ApiAccessor class simplifies handling of oauth2 tokens as used
 * by the OTTO-Market API
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 */
class Oauth2ApiAccessor
{
    private LoggerInterface $log;

    private GenericProvider $provider;

    private AccessTokenInterface $accessToken;

    private Configuration $configuration;

    private Client $httpClient;

    /**
     * Construct a new Oauth2Api-Accessor.
     * If no Guzzle Client instance is provided, a new one will be generated with default settings.
     *
     * @param  Configuration   $configuration
     * @param  LoggerInterface $log
     * @param  Client          $httpClient
     * @throws Oauth2Exception if no valid initial token can be fetched
     */
    public function __construct(Configuration $configuration, LoggerInterface $log, Client $httpClient)
    {
        $this->log = $log;
        $this->log->debug("Setting up the Oauth2ApiAccessor");
        $this->configuration = $configuration;
        $this->httpClient    = $httpClient;

        $this->provider = new GenericProvider(
            [
                'clientId'                => $configuration->getAccessTokenClientId(),
                'urlAccessToken'          => $configuration->getAccessTokenUrl(),
                'urlAuthorize'            => 'TODO: define this properly',
                'urlResourceOwnerDetails' => 'TODO: define this properly',
            ]
        );
        $this->provider->setHttpClient($this->httpClient);
        $this->accessToken = $this->initialToken();
    }

    /**
     * @return AccessToken|AccessTokenInterface
     * @throws Oauth2Exception
     */
    private function initialToken(): AccessTokenInterface
    {
        $this->log->debug("Generating initial access token");
        try {
            $accessToken = $this->provider->getAccessToken(
                'password',
                [
                    'username' => $this->configuration->getAccessTokenUsername(),
                    'password' => $this->configuration->getAccessTokenPassword(),
                ]
            );
            $this->log->debug("Received access token valid until " . ($accessToken->getExpires() ?: "unknown"));
            return $accessToken;
        } catch (IdentityProviderException $e) {
            $this->log->error("Exception attempting to get credentials: " . $e->getMessage());
            throw new Oauth2Exception($e);
        }
    }

    /**
     * @throws Oauth2Exception
     */
    private function refreshToken(): void
    {
        if ($this->accessToken->hasExpired()) {
            $this->log->debug("Refreshing access token");
            try {
                $this->accessToken = $this->provider->getAccessToken(
                    'refresh_token',
                    [
                        'refresh_token' => $this->accessToken->getRefreshToken(),
                    ]
                );
            } catch (IdentityProviderException $e) {
                $this->log->error("Exception attempting to get credentials: " . $e->getTraceAsString());
                throw new Oauth2Exception($e);
            }
        }

        $this->log->debug("Access token is valid (until " . ($this->accessToken->getExpires() ?: "unknown") . ")");
    }

    /**
     * Make a synchronous GET call to an authenticated resource.
     * Token will be automatically fetched and updated, if required.
     *
     * @param  string $path    the path to access (relative to configured base URL)
     * @param  array  $headers headers to add to the request
     * @return ResponseInterface the response
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2Exception if an exception occurs on possible token refresh
     */
    public function get(string $path, array $headers = []): ResponseInterface
    {
        $url = $this->configuration->getApiBasePath() . $path;
        $this->log->debug("Making authenticated GET request to " . $url);
        $this->refreshToken();

        $merged_headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'User-Agent'    => $this->configuration->getUserAgent(),
        ];
        $merged_headers = array_merge($merged_headers, $headers);

        return $this->httpClient->get($url, ['headers' => $merged_headers]);
    }

    /**
     * Make a synchronous POST call to an authenticated resource.
     * Token will be automatically fetched and updated, if required.
     *
     * @param  string $path    the path to access (relative to configured base URL)
     * @param  string $payload the payload to send
     * @param  array  $headers headers to add to the request
     * @return ResponseInterface the response
     * @throws ClientExceptionInterface on HTTP client exception
     * @throws Oauth2Exception if an exception occurs on possible token refresh
     */
    public function post(string $path, string $payload, array $headers = []): ResponseInterface
    {
        $url = $this->configuration->getApiBasePath() . $path;
        $this->log->debug("Making authenticated POST request to " . $url);
        $this->refreshToken();

        $merged_headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'User-Agent'    => $this->configuration->getUserAgent(),
        ];
        $merged_headers = array_merge($merged_headers, $headers);

        return $this->httpClient->post($url, ['headers' => $merged_headers, 'body' => $payload]);
    }
}
