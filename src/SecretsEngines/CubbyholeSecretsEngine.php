<?php

namespace Vault\SecretsEngines;

use Vault\Client;

/**
 * Class CubbyholeSecretsEngine
 *
 * @link https://developer.hashicorp.com/vault/api-docs/secret/cubbyhole Cubbyhole Official Documentation
 * 
 * @package Vault\SecretsEngine
 */
class CubbyholeSecretsEngine extends KeyValueVersion1SecretsEngine
{
    /**
     * @param Client $client Authenticated Vault client
     */
    public function __construct(Client $client)
    {
        parent::__construct(
            $client,
            '/cubbyhole'
        );
    }
}
