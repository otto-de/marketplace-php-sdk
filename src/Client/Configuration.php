<?php

/**
 * Configuration File Doc Comment
 * php version 7.4.15
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 *
 */

namespace Otto\Market\Client;

/**
 * Configuration class stores configurable values used by all classes.
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 */
class Configuration
{

    private string $accessTokenUsername;

    private string $accessTokenPassword;

    private string $accessTokenUrl;

    private string $accessTokenClientId;

    private string $apiBasePath;

    private string $userAgent;

    private int $httpTimeout;

    /**
     * Configuration constructor.
     *
     * @param string $accessTokenUsername
     * @param string $accessTokenPassword
     * @param string $accessTokenUrl
     * @param string $accessTokenClientId
     * @param string $apiBasePath
     * @param string $userAgent
     * @param int    $httpTimeout
     */
    private function __construct(
        string $accessTokenUsername,
        string $accessTokenPassword,
        string $accessTokenUrl,
        string $accessTokenClientId,
        string $apiBasePath,
        string $userAgent,
        int $httpTimeout
    ) {
        $this->accessTokenUsername = $accessTokenUsername;
        $this->accessTokenPassword = $accessTokenPassword;
        $this->accessTokenUrl      = $accessTokenUrl;
        $this->accessTokenClientId = $accessTokenClientId;
        $this->apiBasePath         = $apiBasePath;
        $this->userAgent           = $userAgent;
        $this->httpTimeout         = $httpTimeout;
    }

    public static function forNonlive(string $accessTokenUsername, string $accessTokenPassword): Configuration
    {
        return new Configuration(
            $accessTokenUsername,
            $accessTokenPassword,
            "https://nonlive.api.otto.market/v1/token",
            "token-otto-api",
            "https://nonlive.api.otto.market",
            "php-sdk",
            60
        );
    }

    public static function forLive(string $accessTokenUsername, string $accessTokenPassword): Configuration
    {
        return new Configuration(
            $accessTokenUsername,
            $accessTokenPassword,
            "https://api.otto.market/v1/token",
            "token-otto-api",
            "https://live.api.otto.market",
            "php-sdk",
            60
        );
    }

    public function getAccessTokenUsername(): string
    {
        return $this->accessTokenUsername;
    }

    public function getAccessTokenPassword(): string
    {
        return $this->accessTokenPassword;
    }

    public function getAccessTokenUrl(): string
    {
        return $this->accessTokenUrl;
    }

    public function getAccessTokenClientId(): string
    {
        return $this->accessTokenClientId;
    }

    public function getApiBasePath(): string
    {
        return $this->apiBasePath;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getHttpTimeout(): int
    {
        return $this->httpTimeout;
    }
}
