<?php

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Codeception\Util\Stub;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;
use Vault\Backends\BackendFactory;
use Vault\Client;
use Vault\Exceptions\ClientException;
use Vault\Exceptions\DependencyException;
use Vault\Exceptions\ServerException;
use Vault\Models\Token;
use Vault\Transports\Transport;
use VaultTransports\Guzzle6Transport;

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
     */
    private function getAuthenticatedClient()
    {
        $client = (new Client(new Guzzle6Transport()))
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

        $client = (new Client(new Guzzle6Transport()))
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setCache($cache);

        $this->assertTrue($client->authenticate());

        $realToken = $client->getToken();

        $this->assertNotEmpty($realToken);

        // create new client with cache
        $client = (new Client(new Guzzle6Transport()))->setCache($cache);

        $this->assertTrue($client->authenticate());

        $tokenFromCache = $client->getToken();

        $this->assertNotEmpty($tokenFromCache);
        $this->assertEquals($realToken, $tokenFromCache);
    }

    public function testTryToAuthenticateWithoutStrategy()
    {
        $this->expectException(DependencyException::class);

        (new Client(new Guzzle6Transport()))->authenticate();
    }

    public function testTransportProblems()
    {
        $this->expectException(ServerException::class);

        $transport = Stub::makeEmpty(Transport::class, [
            'createRequest' => function () {
                return Stub::makeEmpty(RequestInterface::class, []);
            },
            'send' => function () {
                throw new TransferException();
            },
        ]);

        (new Client($transport))->get('');
    }

    public function testServerProblems()
    {
        try {
            $transport = Stub::makeEmpty(Transport::class, [
                'createRequest' => function () {
                    return Stub::makeEmpty(RequestInterface::class, []);
                },
                'send' => function () {
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

            (new Client($transport))->get('');
        } catch (Exception $e) {
            $this->assertInstanceOf(ServerException::class, $e);
            $this->assertInstanceOf(ResponseInterface::class, $e->getResponse());
        }
    }

    public function testTokenCacheInvalidate()
    {
        $cache = new ArrayCachePool();

        $client = (new Client(new Guzzle6Transport()))
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setCache($cache);

        $this->assertTrue($client->authenticate());

        $realToken = $client->getToken();

        $this->assertNotEmpty($realToken);

        // create new client with cache
        $client = (new Client(new Guzzle6Transport()))->setCache($cache);

        /** @var CacheItem $token */
        $tokenCacheItem = $cache->getItem(Client::TOKEN_CACHE_KEY);

        $tokenCacheItem->set(new Token(array_merge($tokenCacheItem->get()->toArray(), ['creationTtl' => 0])));

        $cache->save($tokenCacheItem);

        $this->assertTrue($client->authenticate());

        $newToken = $client->getToken();

        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($realToken, $newToken);
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}