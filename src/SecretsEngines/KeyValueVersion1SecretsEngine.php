<?php

namespace Vault\SecretsEngines;

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
    public function read(string $path): Response
    {
        return $this->client->get(
            parent::buildPath($path)
        );
    }

    public function list(string $path): Response
    {
        return $this->client->list(
            parent::buildPath($path)
        );
    }

    public function createOrUpdate(string $path, array $data = []): Response
    {
        return $this->client->post(
            parent::buildPath($path),
            json_encode($data)
        );
    }

    public function delete(string $path): Response
    {
        return $this->client->delete(
            parent::buildPath($path)
        );
    }
}
