<?php

namespace Vault;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vault\AuthenticationStrategies\AuthenticationStrategy;
use Vault\Exceptions\AuthenticationException;
use Vault\Exceptions\DependencyException;
use Vault\Exceptions\RequestException;
use Vault\Exceptions\RuntimeException;
use Vault\Helpers\ModelHelper;
use Vault\Models\Token;
use Vault\ResponseModels\Response;

/**
 * Class Client
 *
 * @todo make an interface
 * @todo add ability to make concurrent requests
 *
 * @package Vault
 */
class Client extends BaseClient
{
    public const TOKEN_CACHE_KEY = 'token';

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var AuthenticationStrategy
     */
    protected $authenticationStrategy;

    /**
     * @param string $path
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function read(string $path): Response
    {
        return $this->get($this->buildPath($path));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function buildPath(string $path): string
    {
        if (!$this->version) {
            $this->logger->warning('API version is not set!');

            return $path;
        }

        return sprintf('/%s%s', $this->version, $path);
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function keys(string $path): Response
    {
        return $this->list($this->buildPath($path));
    }

    /**
     * @param string $path
     * @param array $data
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function write(string $path, array $data = []): Response
    {
        return $this->post($this->buildPath($path), json_encode($data));
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function revoke(string $path): Response
    {
        return $this->delete($this->buildPath($path));
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * @param CacheItemPoolInterface $cache
     *
     * @return $this
     */
    public function setCache(CacheItemPoolInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return AuthenticationStrategy
     */
    public function getAuthenticationStrategy(): AuthenticationStrategy
    {
        return $this->authenticationStrategy;
    }

    /**
     * @param AuthenticationStrategy $authenticationStrategy
     *
     * @return $this
     */
    public function setAuthenticationStrategy(AuthenticationStrategy $authenticationStrategy): self
    {
        $authenticationStrategy->setClient($this);

        $this->authenticationStrategy = $authenticationStrategy;

        return $this;
    }

    /**
     * @inheritdoc
     * @throws DependencyException
     * @throws AuthenticationException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function send(string $method, string $path, string $body = ''): ResponseInterface
    {
        try {
            return parent::send($method, $path, $body);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (RequestException $e) {
            // re-authenticate if 403 and token is expired
            if (
                $this->token &&
                $e->getCode() === 403 &&
                $this->isTokenExpired($this->token)
            ) {
                try {
                    if ($this->authenticate()) {
                        return parent::send($method, $path, $body);
                    }
                } catch (Exception $e) {
                    $this->logger->error('Cannot re-authenticate.', [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ]);

                    $this->logger->debug('Trace.', ['exception' => $e]);
                }

                throw new AuthenticationException('Cannot re-authenticate');
            }

            throw $e;
        }
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    protected function isTokenExpired(Token $token): bool
    {
        return !$token ||
            (
                $token->getCreationTtl() > 0 &&
                time() > $token->getCreationTime() + $token->getCreationTtl()
            );
    }

    /**
     * @return bool
     *
     * @throws RuntimeException
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function authenticate(): bool
    {
        if ($this->token = $this->getTokenFromCache()) {
            $this->logger->debug('Using token from cache.');

            $this->writeTokenInfoToDebugLog();

            return (bool)$this->token;
        }

        if (!$this->authenticationStrategy) {
            $this->logger->critical('Trying to authenticate without strategy.');

            throw new DependencyException(sprintf(
                'Specify authentication strategy before calling this method (%s).',
                __METHOD__
            ));
        }

        $this->logger->debug('Trying to authenticate.');

        if ($auth = $this->authenticationStrategy->authenticate()) {
            $this->logger->debug('Authentication was successful.', ['clientToken' => $auth->getClientToken()]);

            // temporary
            $this->token = new Token(['auth' => $auth]);

            // get info about self
            $response = $this->get('/v1/auth/token/lookup-self');

            $this->token = new Token(array_merge(ModelHelper::camelize($response->getData()), ['auth' => $auth]));

            $this->writeTokenInfoToDebugLog();
            $this->putTokenIntoCache();

            return true;
        }

        return false;
    }

    /**
     * @TODO: move to separated class
     *
     * @return Token|null
     *
     * @throws InvalidArgumentException
     */
    protected function getTokenFromCache(): ?Token
    {
        if (!$this->cache || !$this->cache->hasItem(self::TOKEN_CACHE_KEY)) {
            return null;
        }

        /** @var Token $token */
        $token = $this->cache->getItem(self::TOKEN_CACHE_KEY)->get();

        if (!$token || !$token->getAuth()) {
            $this->logger->debug('No token in cache or auth is empty, returning null.');

            return null;
        }

        // invalidate token
        if ($this->isTokenExpired($token)) {
            $this->logger->debug('Token is expired.');

            $this->writeTokenInfoToDebugLog();

            return null;
        }

        return $token;
    }

    private function writeTokenInfoToDebugLog(): void
    {
        if (!$this->token) {
            $this->logger->debug('Token is null, cannot write info to debug, potential error.');

            return;
        }

        $this->logger->debug('Token info.', [
            'clientToken' => $this->token->getAuth() ? $this->token->getAuth()->getClientToken() : null,
            'id' => $this->token->getId(),
            'creationTime' => $this->token->getCreationTime(),
            'ttl' => $this->token->getCreationTtl(),
        ]);
    }

    /**
     * @TODO: move to separated class
     *
     * @return bool
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    protected function putTokenIntoCache(): bool
    {
        if (!$this->cache) {
            return true; // just ignore
        }

        if ($this->isTokenExpired($this->token)) {
            throw new RuntimeException('Cannot save expired token into cache!');
        }

        $authItem = $this->cache->getItem(self::TOKEN_CACHE_KEY);

        $authItem->set($this->token)->expiresAfter($this->token->getAuth()->getLeaseDuration());

        $this->logger->debug('Token is saved into cache.');

        return $this->cache->save($authItem);
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setToken(Token $token)
    {
        parent::setToken($token);

        $this->putTokenIntoCache();

        return $this;
    }
}
