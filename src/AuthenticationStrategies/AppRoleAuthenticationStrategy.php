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
     * @var string
     */
    protected $appRoleName;

    /**
     * AppRoleAuthenticationStrategy constructor.
     *
     * @param string $roleId
     * @param string $secretId
     */
    public function __construct($roleId, $secretId, $appRoleName = 'approle')
    {
        $this->roleId = $roleId;
        $this->secretId = $secretId;
        $this->appRoleName = $appRoleName;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws \Vault\Exceptions\TransportException
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authenticate()
    {
        $response = $this->client->write(
            '/auth/'.$this->appRoleName.'/login',
            [
                'role_id' => $this->roleId,
                'secret_id' => $this->secretId,
            ]
        );

        return $response->getAuth();
    }
}
