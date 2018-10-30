<?php

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Codeception\Util\Stub;
use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;
use Vault\Client;
use Vault\Exceptions\ClientException;
use Vault\Exceptions\DependencyException;
use Vault\Exceptions\ServerException;
use Vault\Models\Token;
use Vault\ResponseModels\Auth;

class ClientTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testAuthenticationUserPass()
    {
        $this->getAuthenticatedClient();
    }

    /**
     * @return Client
     * @throws \Http\Client\Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getAuthenticatedClient()
    {
        $client = (new Client(new Uri('http://127.0.0.1:8200')))
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setLogger(new NullLogger());

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
        $this->assertNotEmpty($client->getToken()->getAuth()->getLeaseDuration());
        $this->assertNotEmpty($client->getToken()->getAuth()->isRenewable());

        return $client;
    }

    public function testWriteReadRevokeSecret()
    {
        $client = $this->getAuthenticatedClient();

        $this->assertNotEmpty($client->write('/secret/test', ['value' => 'test']));

        $data = $client->read('/secret/test')->getData();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('test', $data['value']);
        $this->assertNotEmpty($client->revoke('/secret/test'));
    }

    public function testWritePermissionDeniedSecret()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $client = $this->getAuthenticatedClient();

        $client->write('/secret/test_prohibited', ['value' => 'test']);
    }

    public function testTokenCache()
    {
        $cache = new ArrayCachePool();

        $client = (new Client(new Uri('http://127.0.0.1:8200')))
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setCache($cache);

        $this->assertTrue($client->authenticate());

        $realToken = $client->getToken();

        $this->assertNotEmpty($realToken);

        // create new client with cache
        $client = (new Client(new Uri('http://127.0.0.1:8200')))->setCache($cache);

        $this->assertTrue($client->authenticate());

        $tokenFromCache = $client->getToken();

        $this->assertNotEmpty($tokenFromCache);
        $this->assertEquals($realToken, $tokenFromCache);
    }

    public function testTryToAuthenticateWithoutStrategy()
    {
        $this->expectException(DependencyException::class);

        (new Client(new Uri('http://127.0.0.1:8200')))->authenticate();
    }

    public function testServerProblems()
    {
        try {
            $httpClient = Stub::makeEmpty(HttpClient::class, [
                'sendRequest' => function () {
                    return Stub::makeEmpty(ResponseInterface::class, [
                        'getStatusCode' => function () {
                            return 500;
                        },
                        'getReasonPhrase' => function () {
                            return '';
                        },
                        'getHeaders' => function () {
                            return [];
                        },
                        'getBody' => function () {
                            return Stub::makeEmpty(StreamInterface::class, [
                                'getContents' => function () {
                                    return '';
                                },
                            ]);
                        },
                    ]);
                },
            ]);

            (new Client(new Uri('http://127.0.0.1:8200'), $httpClient))->get('');
        } catch (Exception $e) {
            $this->assertInstanceOf(ServerException::class, $e);
            $this->assertInstanceOf(ResponseInterface::class, $e->getResponse());
        }
    }

    public function testTokenCacheInvalidate()
    {
        $cache = new ArrayCachePool();

        $client = (new Client(new Uri('http://127.0.0.1:8200')))
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
        $client = (new Client(new Uri('http://127.0.0.1:8200')))->setCache($cache);

        /** @var CacheItem $token */
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

    protected function setUp()
    {
        \VCR\VCR::turnOn();

        \VCR\VCR::insertCassette('unit-client');

        return parent::setUp();
    }

    protected function tearDown()
    {
        // To stop recording requests, eject the cassette
        \VCR\VCR::eject();

        // Turn off VCR to stop intercepting requests
        \VCR\VCR::turnOff();

        parent::tearDown();
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
