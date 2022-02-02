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
    public function build(ResponseInterface $response): Response
    {
        $rawData = json_decode((string)$response->getBody(), true) ?: [];
        $data = ModelHelper::camelize($rawData);
        $data['data'] = $rawData['data'] ?? [];

        if (array_key_exists('auth', $data) && $data['auth']) {
            $data['auth'] = new Auth($data['auth']);
        }

        return new Response($data);
    }
}
