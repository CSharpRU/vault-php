<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;
use Vault\ResponseModels\Auth;

/**
 * Class AppRoleAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class AppRoleAuthenticationStrategy extends AbstractAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $roleId;

    /**
     * @var string
     */
    protected $secretId;

    /**
     * @var string
     */
    protected $name;

    /**
     * AppRoleAuthenticationStrategy constructor.
     *
     * @param string $roleId
     * @param string $secretId
     * @param string $name
     */
    public function __construct($roleId, $secretId, $name = 'approle')
    {
        $this->roleId = $roleId;
        $this->secretId = $secretId;
        $this->name = $name;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws ClientExceptionInterface
     */
    public function authenticate(): ?Auth
    {
        $response = $this->client->write(
            '/auth/' . $this->name . '/login',
            [
                'role_id' => $this->roleId,
                'secret_id' => $this->secretId,
            ]
        );

        return $response->getAuth();
    }
}
