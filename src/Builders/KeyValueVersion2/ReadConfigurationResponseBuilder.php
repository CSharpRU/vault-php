<?php

namespace Vault\Builders\KeyValueVersion2;

use Vault\Helpers\ModelHelper;
use Vault\Models\KeyValueVersion2\Configuration;
use Vault\ResponseModels\Response;

/**
 * Class ReadConfigurationResponseBuilder
 *
 * @package Vault\Builder\KeyValueVersion2
 */
class ReadConfigurationResponseBuilder
{
    /**
     * @param Response $response
     *
     * @return Configuration
     */
    public static function build(Response $response): Configuration
    {
        $data = $response->getData();
        
        return new Configuration(ModelHelper::camelize($data, false));
    }
}
