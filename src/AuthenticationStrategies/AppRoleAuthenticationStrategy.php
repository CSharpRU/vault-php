<?php

namespace Vault\AuthenticationStrategies;

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
     * AppRoleAuthenticationStrategy constructor.
     *
     * @param string $roleId
     * @param string $secretId
     */
    public function __construct($roleId, $secretId)
    {
        $this->roleId = $roleId;
        $this->secretId = $secretId;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws \Http\Client\Exception
     */
    public function authenticate()
    {
        $response = $this->client->write(
            '/auth/approle/login',
            [
                'role_id' => $this->roleId,
                'secret_id' => $this->secretId,
            ]
        );

        return $response->getAuth();
    }
}
