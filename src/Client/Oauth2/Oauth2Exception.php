<?php

/**
 * Oauth2Exception File Doc Comment
 * php version 7.4.15
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 *
 */

namespace Otto\Market\Client\Oauth2;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Otto\Market\Client\OttoMarketClientException;

/**
 * Oauth2Exception class shows errors regarding oauth2 workflows
 *
 * @license  https://opensource.org/licenses/Apache-2.0 Apache-2.0
 * @link     https://public-docs.live.api.otto.market
 */
class Oauth2Exception extends OttoMarketClientException
{
    public function __construct(IdentityProviderException $previous)
    {
        parent::__construct("Exception when attempting to fetch/update API token", 0, $previous);
    }
}
