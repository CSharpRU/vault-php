<?php

namespace Vault\Builders\KeyValueVersion1;

use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\KeyValueVersion1\ListResponse;
use Vault\ResponseModels\Response;

/**
 * Class ListResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion1
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
