<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;
use Vault\ResponseModels\Auth;

/**
 * Class UserPassAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class UserPassAuthenticationStrategy extends AbstractAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $authMethod;

    /**
     * UserPassAuthenticationStrategy constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $authMethod
     */
    public function __construct($username, $password, $authMethod = 'userpass')
    {
        $this->username = $username;
        $this->password = $password;
        $this->authMethod = $authMethod;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws ClientExceptionInterface
     */
    public function authenticate(): Auth
    {
        $response = $this->client->write(
            sprintf('/auth/%s/login/%s', $this->authMethod, $this->username),
            ['password' => $this->password]
        );

        return $response->getAuth();
    }
}
