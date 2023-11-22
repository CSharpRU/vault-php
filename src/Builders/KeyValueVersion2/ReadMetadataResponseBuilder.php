<?php

namespace Vault\Builders\KeyValueVersion2;

use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\KeyValueVersion2\ReadMetadataResponse;
use Vault\ResponseModels\KeyValueVersion2\VersionMetadata;
use Vault\ResponseModels\Response;

/**
 * Class ReadMetadataResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion2
 */
class ReadMetadataResponseBuilder
{
    /**
     * @param Response $response
     *
     * @return ReadMetadataResponse
     */
    public static function build(Response $response): ReadMetadataResponse
    {
        $data = $response->getData();
        
        $versions = [];
        foreach ($data['versions'] as $versionNumber => $versionData) {
            $versionData['custom_metadata'] = $data['custom_metadata'] ?? null;
            $versionData['version'] = $versionNumber;
            $versions[$versionNumber] = new VersionMetadata(ModelHelper::camelize($versionData, false));
        }
        $data['versions'] = $versions;
        
        return new ReadMetadataResponse(ModelHelper::camelize($data, false));
    }
}
