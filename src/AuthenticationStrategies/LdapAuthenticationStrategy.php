<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;
use Vault\ResponseModels\Auth;

/**
 * Class LdapAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class LdapAuthenticationStrategy extends UserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function authenticate(): Auth
    {
        $response = $this->client->write(
            sprintf('/auth/ldap/login/%s', $this->username),
            ['password' => $this->password]
        );

        return $response->getAuth();
    }
}
