<?php

use Cache\Adapter\PHPArray\ArrayCachePool;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;
use Vault\Client;
use Vault\Exceptions\AuthenticationException;
use Vault\Exceptions\DependencyException;
use Vault\Exceptions\RequestException;
use Vault\Exceptions\RuntimeException;
use Vault\Models\Token;
use Vault\ResponseModels\Auth;
use VCR\VCR;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;

class ClientTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    public function testAuthenticationUserPass(): void
    {
        $this->getAuthenticatedClient();
    }

    /**
     * @return Client
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    private function getAuthenticatedClient(): Client
    {
        $client = $this->getClient()
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'));

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
        $this->assertNotEmpty($client->getToken()->getAuth()->getLeaseDuration());
        $this->assertNotEmpty($client->getToken()->getAuth()->isRenewable());

        return $client;
    }

    /**
     * @param ClientInterface|null $client
     *
     * @return Client
     */
    private function getClient(?ClientInterface $client = null): Client
    {
        return new Client(
            new Uri('http://127.0.0.1:8200'),
            $client ?: new \AlexTartan\GuzzlePsr18Adapter\Client(),
            new RequestFactory(),
            new StreamFactory()
        );
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    public function testWriteReadRevokeSecret(): void
    {
        $client = $this->getAuthenticatedClient();

        $this->assertNotEmpty($client->write('/secret/test', ['value' => 'test']));

        $data = $client->read('/secret/test')->getData();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('test', $data['value']);
        $this->assertNotEmpty($client->revoke('/secret/test'));
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    public function testWritePermissionDeniedSecret(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(403);

        $client = $this->getAuthenticatedClient();

        $client->write('/secret/test_prohibited', ['value' => 'test']);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    public function testTokenCache(): void
    {
        $cache = new ArrayCachePool();

        $client = $this->getClient()
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setCache($cache);

        $this->assertTrue($client->authenticate());

        $realToken = $client->getToken();

        $this->assertNotEmpty($realToken);

        // create new client with cache
        $client = $this->getClient()->setCache($cache);

        $this->assertTrue($client->authenticate());

        $tokenFromCache = $client->getToken();

        $this->assertNotEmpty($tokenFromCache);
        $this->assertEquals($realToken, $tokenFromCache);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    public function testTryToAuthenticateWithoutStrategy(): void
    {
        $this->expectException(DependencyException::class);

        $this->getClient()->authenticate();
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testServerProblems(): void
    {
        try {
            $client = Stub::makeEmpty(ClientInterface::class, [
                'sendRequest' => function () {
                    throw new RequestException('', 500);
                },
            ]);

            $this->getClient($client)->get('');
        } catch (Exception $e) {
            $this->assertInstanceOf(RequestException::class, $e);
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     * @throws Exception
     */
    public function testReAuthentication(): void
    {
        $httpClient = Stub::makeEmpty(ClientInterface::class, [
            'sendRequest' => function (RequestInterface $request) {
                static $requestCounter = 0;

                if ($requestCounter === 0) {
                    $requestCounter++;

                    throw new RequestException('', 403);
                }

                return (new \AlexTartan\GuzzlePsr18Adapter\Client())->sendRequest($request);
            },
        ]);

        $client = $this->getClient($httpClient)
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setToken(new Token([
                'auth' => new Auth(['clientToken' => 123]),
                'creationTtl' => (new DateTime())->getTimestamp() - 1,
                'ttl' => 1,
            ]));

        $this->assertNotEmpty($client->write('/secret/test', ['value' => 'test']));
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testReAuthenticationFailure(): void
    {
        try {
            $httpClient = Stub::makeEmpty(ClientInterface::class, [
                'sendRequest' => function () {
                    throw new RequestException('', 403);
                },
            ]);

            $client = $this->getClient($httpClient)
                ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
                ->setToken(new Token([
                    'auth' => new Auth(['clientToken' => 123]),
                    'creationTtl' => (new DateTime())->getTimestamp() - 1,
                    'ttl' => 1,
                ]));

            $client->get('');
        } catch (Exception $e) {
            $this->assertInstanceOf(AuthenticationException::class, $e);
        }
    }

    /**
     * @throws RuntimeException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function testTokenCacheInvalidate(): void
    {
        $cache = new ArrayCachePool();

        $client = $this->getClient()
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setCache($cache)
            ->setToken(new Token([
                'auth' => new Auth(['clientToken' => '123']),
                'creationTime' => time(),
                'creationTtl' => 300,
            ]));

        $realToken = $client->getToken();

        $this->assertNotEmpty($realToken);

        // create new client with cache
        $client = $this->getClient()->setCache($cache);

        $tokenCacheItem = $cache->getItem(Client::TOKEN_CACHE_KEY);

        $tokenAsArray = $tokenCacheItem->get()->toArray();

        $tokenAsArray['auth'] = new Auth($tokenAsArray['auth']);

        $tokenCacheItem->set(new Token(array_merge($tokenAsArray, ['creationTtl' => 0])));

        $cache->save($tokenCacheItem);

        $this->assertTrue($client->authenticate());

        $newToken = $client->getToken();

        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($realToken, $newToken);
    }

    protected function setUp(): void
    {
        VCR::turnOn();

        VCR::insertCassette('unit-client');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        // To stop recording requests, eject the cassette
        VCR::eject();

        // Turn off VCR to stop intercepting requests
        VCR::turnOff();

        parent::tearDown();
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
