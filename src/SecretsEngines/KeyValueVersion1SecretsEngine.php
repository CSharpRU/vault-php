<?php

namespace Vault\SecretsEngines;

use Vault\Builders\KeyValueVersion1\ListResponseBuilder;
use Vault\ResponseModels\KeyValueVersion1\ListResponse;
use Vault\ResponseModels\Response;

/**
 * Class KeyValueVersion1SecretsEngine
 *
 * @link https://developer.hashicorp.com/vault/api-docs/secret/kv/kv-v1 Key/Value Version 1 Official Documentation
 *
 * @package Vault\SecretsEngine
 */
class KeyValueVersion1SecretsEngine extends AbstractSecretsEngine
{
    /**
     * Read specified secret
     * 
     * @param string $path Path of the secret
     * @return Response
     **/
    public function read(string $path): Response
    {
        return $this->client->get(
            parent::buildPath($path)
        );
    }

    /**
     * List secrets at specified path
     * 
     * @param string $path Path to list secrets from
     * @return ListResponse
     **/
    public function list(string $path): ListResponse
    {
        return ListResponseBuilder::build(
            $this->client->list(
                parent::buildPath($path)
            )
        );
    }

    /**
     * Create or update specified secret
     * 
     * @param string $path Path of the secret
     * @param array $data Payload to write
     * @return Response
     **/
    public function createOrUpdate(string $path, array $data = []): Response
    {
        return $this->client->post(
            parent::buildPath($path),
            json_encode($data)
        );
    }

    /**
     * Delete secret
     * 
     * @param string $path Path of the secret
     * @return Response
     **/
    public function delete(string $path): Response
    {
        return $this->client->delete(
            parent::buildPath($path)
        );
    }
}
