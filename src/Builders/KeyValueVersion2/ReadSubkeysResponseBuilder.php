<?php

namespace Vault\Builders\KeyValueVersion2;

use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\KeyValueVersion2\ReadSubkeysResponse;
use Vault\ResponseModels\KeyValueVersion2\VersionMetadata;
use Vault\ResponseModels\Response;

/**
 * Class ReadSubkeysResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion2
 */
class ReadSubkeysResponseBuilder
{
    /**
     * @param Response $response
     *
     * @return ReadSubkeysResponse
     */
    public static function build(Response $response): ReadSubkeysResponse
    {
        $data = $response->getData();
        
        $data['metadata'] = new VersionMetadata(ModelHelper::camelize($data['metadata'], false));
        
        return new ReadSubkeysResponse($data);
    }
}
