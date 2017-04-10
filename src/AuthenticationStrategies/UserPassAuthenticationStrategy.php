<?php

namespace Vault\AuthenticationStrategies;

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
     * UserPassAuthenticationStrategy constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authenticate()
    {
        $response = $this->client->post(sprintf('/v1/auth/userpass/login/%s', $this->username), [
            'json' => ['password' => $this->password],
        ]);

        return $response->getAuth();
    }
}