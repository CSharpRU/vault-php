<?php

namespace Vault\AuthenticationStrategies;

use Aws\Middleware;
use Aws\Sts\StsClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Vault\Exceptions\AuthenticationException;
use Vault\ResponseModels\Auth;

/**
 * Class AwsIamAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class AwsIamAuthenticationStrategy extends AbstractAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $methodPathSegment = 'aws';

    /** @var string */
    private $role;

    /** @var string */
    private $region;

    /** @var ?string */
    private $serverId;

    /**
     * @param string     $role The name of the vault role
     * @param string     $region The AWS region to use
     * @param ?string    $vaultServerId If set, this string is used as X-Vault-AWS-IAM-Server-ID, to protect against replay attacks
     * @param ?StsClient $stsClient Custom instance of StsClient
     */
    public function __construct(
        $role,
        $region,
        $vaultServerId = null,
        $stsClient = null
    ) {
        $this->role = $role;
        $this->region = $region;
        $this->serverId = $vaultServerId;
        $this->stsClient = $stsClient;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws AuthenticationException
     * @throws ClientExceptionInterface
     */
    public function authenticate(): Auth
    {
        if (!$this->methodPathSegment) {
            throw new AuthenticationException('methodPathSegment must be set before usage');
        }

        if (!$this->stsClient) {
            $this->stsClient = new StsClient([
                'region' => $this->region,
                'version' => 'latest',
                'sts_regional_endpoints' => 'regional',
            ]);
        }


        // Creating a signed command, to get the parameters for the actual login-request to vault
        $command = $this->stsClient->getCommand('GetCallerIdentity');

        if ($this->serverId) {
            $command->getHandlerList()->appendBuild(
                Middleware::mapRequest(function (RequestInterface $request) {
                    return $request->withHeader('X-Vault-AWS-IAM-Server-ID', $this->serverId);
                }),
                'add-header'
            );
        }

        $request = \Aws\serialize($command);

        $response = $this->client->write(
            sprintf('/auth/%s/login', $this->methodPathSegment),
            [
                'role' => $this->role,
                'iam_http_request_method' => $request->getMethod(),
                'iam_request_url' => base64_encode($request->getUri()),
                'iam_request_body' => base64_encode($request->getBody()),
                'iam_request_headers' => base64_encode(json_encode($request->getHeaders())),
            ]
        );

        return $response->getAuth();
    }

    /**
     * @return string
     */
    public function getMethodPathSegment(): string
    {
        return $this->methodPathSegment;
    }

    /**
     * @param string $methodPathSegment
     *
     * @return static
     */
    public function setMethodPathSegment(string $methodPathSegment)
    {
        $this->methodPathSegment = $methodPathSegment;

        return $this;
    }
}