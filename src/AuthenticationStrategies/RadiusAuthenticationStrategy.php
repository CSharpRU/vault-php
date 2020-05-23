<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;
use Vault\ResponseModels\Auth;

/**
 * Class RadiusAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class RadiusAuthenticationStrategy extends UserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function authenticate(): Auth
    {
        $response = $this->client->write(
            sprintf('/auth/radius/login/%s', $this->username),
            ['password' => $this->password]
        );

        return $response->getAuth();
    }
}
