<?php

namespace Vault\Builders\KeyValueVersion2;

use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\KeyValueVersion2\ReadResponse;
use Vault\ResponseModels\KeyValueVersion2\VersionMetadata;
use Vault\ResponseModels\Response;

/**
 * Class ReadResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion2
 */
class ReadResponseBuilder
{
    /**
     * @param Response $response
     *
     * @return ReadResponse
     */
    public static function build(Response $response): ReadResponse
    {
        $data = $response->getData();
        
        $data['metadata'] = new VersionMetadata(ModelHelper::camelize($data['metadata'], false));
        
        return new ReadResponse($data);
    }
}
