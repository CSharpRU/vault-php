<?php

namespace Vault\Builders\KeyValueVersion2;

use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\KeyValueVersion2\VersionMetadata;
use Vault\ResponseModels\Response;

/**
 * Class VersionMetadataResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion2
 */
class VersionMetadataResponseBuilder
{
    /**
     * @param Response $response
     *
     * @return VersionMetadata
     */
    public static function build(Response $response): VersionMetadata
    {
        $data = $response->getData();
        
        return new VersionMetadata(ModelHelper::camelize($data, false));
    }
}
