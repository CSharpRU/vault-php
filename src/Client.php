<?php

namespace Vault;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Vault\AuthenticationStrategies\AuthenticationStrategy;
use Vault\Exceptions\DependencyException;
use Vault\Helpers\ModelHelper;
use Vault\Models\Token;
use Vault\ResponseModels\Response;

/**
 * Class Client
 *
 * @package Vault
 */
class Client extends BaseClient
{
    const TOKEN_CACHE_KEY = 'token';

    /**
     * Threshold of re-authentication attempts.
     *
     * @var int
     */
    protected $reAuthenticationThreshold = 3;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var AuthenticationStrategy
     */
    protected $authenticationStrategy;

    /**
     * @var int
     */
    private $reAuthenticationCounter = 0;

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function read($path)
    {
        return $this->get($this->buildPath($path));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function buildPath($path)
    {
        if (!$this->version) {
            $this->logger->warning('API version is not set!');

            return $path;
        }

        return sprintf('/%s%s', $this->version, $path);
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function write($path, array $data = [])
    {
        return $this->post($this->buildPath($path), ['body' => json_encode($data)]);
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function revoke($path)
    {
        return $this->delete($this->buildPath($path));
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheItemPoolInterface $cache
     *
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return AuthenticationStrategy
     */
    public function getAuthenticationStrategy()
    {
        return $this->authenticationStrategy;
    }

    /**
     * @param AuthenticationStrategy $authenticationStrategy
     *
     * @return $this
     */
    public function setAuthenticationStrategy(AuthenticationStrategy $authenticationStrategy)
    {
        $authenticationStrategy->setClient($this);

        $this->authenticationStrategy = $authenticationStrategy;

        return $this;
    }

    /**
     * @return int
     */
    public function getReAuthenticationThreshold()
    {
        return $this->reAuthenticationThreshold;
    }

    /**
     * @param int $reAuthenticationThreshold
     *
     * @return $this
     */
    public function setReAuthenticationThreshold($reAuthenticationThreshold)
    {
        $this->reAuthenticationThreshold = $reAuthenticationThreshold > 0 ? $reAuthenticationThreshold : 0;

        return $this;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function isNeedToRetryRequest(ResponseInterface $response)
    {
        // start re-authentication process if got 403 code and token is expired
        // isReAuthenticationInProgress check prevents additional calls to reAuthenticate method
        return $response->getStatusCode() === 403 &&
            !$this->isReAuthenticationInProgress() &&
            $this->isTokenExpired($this->token) &&
            $this->reAuthenticate();
    }

    /**
     * @return bool
     */
    protected function isReAuthenticationInProgress()
    {
        return $this->reAuthenticationCounter > 0;
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    protected function isTokenExpired($token)
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function reAuthenticate()
    {
        // increment counter for isReAuthenticationInProgress
        $this->reAuthenticationCounter++;

        // stop recursion if we're greater or equal to the threshold
        if ($this->reAuthenticationCounter >= $this->reAuthenticationThreshold) {
            return false;
        }

        try {
            $this->logger->debug('Trying to re-authenticate.', ['attempt' => $this->reAuthenticationCounter]);

            if (!$this->authenticate()) {
                throw new \RuntimeException('Cannot re-authenticate.');
            }

            return true;
        } catch (\Exception $e) {
            // just skip all exceptions
        }

        return $this->reAuthenticate();
    }

    /**
     * @return bool
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\DependencyException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authenticate()
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
            $this->resetReAuthenticationCounter();

            return true;
        }

        return false;
    }

    /**
     * @TODO: move to separated class
     *
     * @return Token|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getTokenFromCache()
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

    private function writeTokenInfoToDebugLog()
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
     */
    protected function putTokenIntoCache()
    {
        if (!$this->cache) {
            return true; // just ignore
        }

        $authItem = (new CacheItem(self::TOKEN_CACHE_KEY))
            ->set($this->token)
            ->expiresAfter($this->token->getAuth()->getLeaseDuration());

        $this->logger->debug('Token is saved into cache.');

        return $this->cache->save($authItem);
    }

    private function resetReAuthenticationCounter()
    {
        $this->reAuthenticationCounter = 0;
    }
}
