<?php


use Cache\Adapter\PHPArray\ArrayCachePool;
use Codeception\Util\Stub;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategy\UserPassAuthenticationStrategy;
use Vault\Backend\BackendFactory;
use Vault\Client;
use Vault\Exception\ClassNotFoundException;
use Vault\Exception\ClientException;
use Vault\Exception\DependencyException;
use Vault\Exception\ServerException;

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
        $client = (new Client())
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setLogger(new NullLogger());

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());

        return $client;
    }

    public function testWriteReadRevokeSecret()
    {
        $secretBackend = $this->getSecretBackend();

        $this->assertTrue($secretBackend->write('test', ['value' => 'test']));

        $data = $secretBackend->read('test');

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('test', $data['value']);
        $this->assertTrue($secretBackend->revoke('test'));
    }

    /**
     * @return \Vault\Backend\Backend
     */
    private function getSecretBackend(): \Vault\Backend\Backend
    {
        return BackendFactory::getBackend($this->getAuthenticatedClient(), BackendFactory::BACKEND_SECRET);
    }

    public function testWritePermissionDeniedSecret()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $secretBackend = $this->getSecretBackend();

        $secretBackend->write('test_prohibited', ['value' => 'test']);
    }

    public function testUnknownBackend()
    {
        $this->expectException(ClassNotFoundException::class);

        BackendFactory::getBackend($this->getAuthenticatedClient(), 'unknown');
    }

    public function testTokenCache()
    {
        $cache = new ArrayCachePool();

        $client = (new Client())
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setCache($cache);

        $this->assertTrue($client->authenticate());

        $realToken = $client->getToken();

        $this->assertNotEmpty($realToken);

        // create new client with cache
        $client = (new Client())->setCache($cache);

        $this->assertTrue($client->authenticate());

        $tokenFromCache = $client->getToken();

        $this->assertNotEmpty($tokenFromCache);
        $this->assertEquals($realToken, $tokenFromCache);
    }

    public function testTryToAuthenticateWithoutStrategy()
    {
        $this->expectException(DependencyException::class);

        (new Client())->authenticate();
    }

    public function testTransportProblems()
    {
        $this->expectException(ServerException::class);

        $transport = Stub::makeEmpty(ClientInterface::class, [
            'send' => function () {
                throw new TransferException();
            },
        ]);

        (new Client([], null, $transport))->get('');
    }

    public function testServerProblems()
    {
        try {
            $transport = Stub::makeEmpty(ClientInterface::class, [
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

            (new Client([], null, $transport))->get('');
        } catch (Exception $e) {
            $this->assertInstanceOf(ServerException::class, $e);
            $this->assertInstanceOf(ResponseInterface::class, $e->getResponse());
        }
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}