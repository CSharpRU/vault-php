<?php

namespace Vault\SecretsEngines;

use Vault\Builders\KeyValueVersion2\ListResponseBuilder;
use Vault\Builders\KeyValueVersion2\ReadConfigurationResponseBuilder;
use Vault\Builders\KeyValueVersion2\ReadMetadataResponseBuilder;
use Vault\Builders\KeyValueVersion2\ReadResponseBuilder;
use Vault\Builders\KeyValueVersion2\ReadSubkeysResponseBuilder;
use Vault\Builders\KeyValueVersion2\VersionMetadataResponseBuilder;
use Vault\Models\KeyValueVersion2\Configuration;
use Vault\Models\KeyValueVersion2\SecretMetadata;
use Vault\Models\KeyValueVersion2\WriteOptions;
use Vault\ResponseModels\KeyValueVersion2\ListResponse;
use Vault\ResponseModels\KeyValueVersion2\ReadMetadataResponse;
use Vault\ResponseModels\KeyValueVersion2\ReadResponse;
use Vault\ResponseModels\KeyValueVersion2\ReadSubkeysResponse;
use Vault\ResponseModels\KeyValueVersion2\VersionMetadata;
use Vault\ResponseModels\Response;

/**
 * Class KeyValueVersion2SecretsEngine
 *
 * @link https://developer.hashicorp.com/vault/api-docs/secret/kv/kv-v2 Key/Value Version 2 Official Documentation
 *
 * @package Vault\SecretsEngine
 */
class KeyValueVersion2SecretsEngine extends AbstractSecretsEngine
{
    /**
     * Configures secrets engine
     * 
     * @param Configuration $config Configuration to set
     * @return Response
     **/
    public function configure(Configuration $config): Response
    {
        return $this->client->post(
            parent::buildPath('config'),
            json_encode($config)
        );
    }

    /**
     * Reads current secrets engine configuration
     * 
     * @return Configuration
     **/
    public function readConfiguration(): Configuration
    {
        return ReadConfigurationResponseBuilder::build(
            $this->client->get(
                parent::buildPath('config')
            )
        );
    }

    /**
     * Read specified secret version
     * 
     * @param string $path Path of the secret
     * @param int $version Version to read (0 = latest)
     * @return ReadResponse
     **/
    public function read(string $path, int $version = 0): ReadResponse
    {
        return ReadResponseBuilder::build(
            $this->client->get(
                parent::buildPath(
                    sprintf('data/%s?version=%d', $path, $version)
                )
            )
        );
    }

    /**
     * Creates new version of a secret
     * 
     * @param string $path Path of the secret
     * @param array $data Payload to write
     * @param WriteOptions|null $options Write options
     * @return VersionMetadata
     **/
    public function createOrUpdate(string $path, array $data = [], ?WriteOptions $options = null): VersionMetadata
    {
        $payload = [
            'data' => $data,
        ];
        if ($options) {
            $payload['options'] = $options;
        }
        return VersionMetadataResponseBuilder::build(
            $this->client->post(
                parent::buildPath('data/'.$path),
                json_encode($payload)
            )
        );
    }

    /**
     * Patches existing secret
     * 
     * @param string $path Path of the secret
     * @param array $data Payload to write
     * @param WriteOptions|null $options Write options
     * @return VersionMetadata
     **/
    public function patch(string $path, array $data = [], ?WriteOptions $options = null): VersionMetadata
    {
        $payload = [
            'data' => $data,
        ];
        if ($options) {
            $payload['options'] = $options;
        }
        return VersionMetadataResponseBuilder::build(
            $this->client->patch(
                parent::buildPath('data/'.$path),
                json_encode($payload)
            )
        );
    }

    /**
     * Reads subkeys within a secret
     * 
     * @param string $path Path of the secret
     * @param int $version Version to read (0 = latest)
     * @param int $depth Deepest nesting level (0 = no limit)
     * @return ReadSubkeysResponse
     **/
    public function readSubkeys(string $path, int $version = 0, int $depth = 0): ReadSubkeysResponse
    {
        return ReadSubkeysResponseBuilder::build(
            $this->client->get(
                parent::buildPath(
                    sprintf('subkeys/%s?version=%d&depth=%d', $path, $version, $depth)
                )
            )
        );
    }

    /**
     * Deletes latest version of the secret
     * 
     * @param string $path Path of the secret
     * @return Response
     **/
    public function deleteLatest(string $path): Response
    {
        return $this->client->delete(
            parent::buildPath('data/'.$path)
        );
    }

    /**
     * Deletes specified secret versions
     * 
     * @param string $path Path of the secret
     * @param int[] $versions Versions to delete
     * @return Response
     **/
    public function deleteVersions(string $path, array $versions = []): Response
    {
        $payload = [
            'versions' => $versions
        ];
        return $this->client->post(
            parent::buildPath('delete/'.$path),
            json_encode($payload)
        );
    }

    /**
     * Undeletes specified secret versions
     * 
     * @param string $path Path of the secret
     * @param int[] $versions Versions to delete
     * @return Response
     **/
    public function undeleteVersions(string $path, array $versions = []): Response
    {
        $payload = [
            'versions' => $versions
        ];
        return $this->client->post(
            parent::buildPath('undelete/'.$path),
            json_encode($payload)
        );
    }

    /**
     * Destroys (hard delete) specified secret versions
     * 
     * @param string $path Path of the secret
     * @param int[] $versions Versions to delete
     * @return Response
     **/
    public function destroyVersions(string $path, array $versions = []): Response
    {
        $payload = [
            'versions' => $versions
        ];
        return $this->client->put(
            parent::buildPath('destroy/'.$path),
            json_encode($payload)
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
                parent::buildPath('metadata/'.$path)
            )
        );
    }

    /**
     * Reads specified secret metadata
     * 
     * @param string $path Path of the secret
     * @return ReadMetadataResponse
     **/
    public function readMetadata(string $path): ReadMetadataResponse
    {
        return ReadMetadataResponseBuilder::build(
            $this->client->get(
                parent::buildPath('metadata/'.$path)
            )
        );
    }

    /**
     * Creates or updates specified secret metadata
     * 
     * @param string $path Path of the secret
     * @param SecretMetadata $metadata Metadata to set
     * @return Response
     **/
    public function createOrUpdateMetadata(string $path, SecretMetadata $metadata): Response
    {
        return $this->client->post(
            parent::buildPath('metadata/'.$path),
            json_encode($metadata)
        );
    }

    /**
     * Patches specified secret metadata
     * 
     * @param string $path Path of the secret
     * @param array $metadata Metadata to set
     * @return Response
     **/
    public function patchMetadata(string $path, array $metadata): Response
    {
        return $this->client->patch(
            parent::buildPath('metadata/'.$path),
            json_encode($metadata)
        );
    }

    /**
     * Deletes metadata and all versions
     * 
     * @param string $path Path of the secret
     * @return Response
     **/
    public function deleteMetadata(string $path): Response
    {
        return $this->client->delete(
            parent::buildPath('metadata/'.$path)
        );
    }
}
