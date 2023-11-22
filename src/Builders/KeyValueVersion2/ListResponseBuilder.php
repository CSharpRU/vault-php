<?php

namespace Vault\Builders\KeyValueVersion2;

use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\KeyValueVersion2\ListResponse;
use Vault\ResponseModels\Response;

/**
 * Class ListResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion2
 */
class ListResponseBuilder
{
    /**
     * @param Response $response
     *
     * @return ListResponse
     */
    public static function build(Response $response): ListResponse
    {
        $data = $response->getData();
        
        return new ListResponse(ModelHelper::camelize($data, false));
    }
}
