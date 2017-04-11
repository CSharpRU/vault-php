<?php

namespace Vault\Builders;

use Psr\Http\Message\ResponseInterface;
use Vault\Helpers\ArrayHelper;
use Vault\Helpers\ModelHelper;
use Vault\ResponseModels\Auth;
use Vault\ResponseModels\Response;

/**
 * Class ResponseBuilder
 *
 * @package Vault\Builder
 */
class ResponseBuilder
{
    /**
     * @param ResponseInterface $response
     *
     * @return Response
     */
    public function build(ResponseInterface $response)
    {
        $data = ModelHelper::camelize(json_decode((string)$response->getBody(), true) ?: []);

        if ($auth = ArrayHelper::getValue($data, 'auth')) {
            $data['auth'] = new Auth($auth);
        }

        return new Response($data);
    }
}