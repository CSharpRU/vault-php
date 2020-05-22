<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;
use Vault\ResponseModels\Auth;

/**
 * Class OktaAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class OktaAuthenticationStrategy
    extends UserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function authenticate(): Auth
    {
        $response = $this->client->write(
            sprintf('/auth/okta/login/%s', $this->username),
            ['password' => $this->password]
        );

        return $response->getAuth();
    }
}
