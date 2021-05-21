<?php

use AlexTartan\GuzzlePsr18Adapter\Client;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Codeception\Test\Unit;
use Psr\Http\Client\ClientExceptionInterface;
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;
use Vault\CachedClient;
use Vault\ResponseModels\Response;
use VCR\VCR;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\Uri;

class CachedClientTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws \Vault\Exceptions\RuntimeException
     */
    public function testReadCache(): void
    {
        $client = $this->getAuthenticatedClient()->enableReadCache()->setCache(new ArrayCachePool());

        $this->assertNotEmpty($client->write('/secret/test', ['value' => 'test']));

        $data = $client->read('/secret/test')->getData();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('test', $data['value']);
        $this->assertNotEmpty($client->revoke('/secret/test'));
        $this->assertTrue($client->getCache()->hasItem(CachedClient::READ_CACHE_KEY . '_secret_test'));
    }

    /**
     * @return CachedClient
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws \Vault\Exceptions\RuntimeException
     */
    private function getAuthenticatedClient(): CachedClient
    {
        $client = new CachedClient(
            new Uri('http://127.0.0.1:8200'),
            new Client(),
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'));

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
        $this->assertNotEmpty($client->getToken()->getAuth()->getLeaseDuration());
        $this->assertNotEmpty($client->getToken()->getAuth()->isRenewable());

        return $client;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws \Vault\Exceptions\RuntimeException
     */
    public function testReadCacheKeyAlreadyInCache(): void
    {
        $client = $this->getAuthenticatedClient()->enableReadCache()->setCache(new ArrayCachePool());
        $key = CachedClient::READ_CACHE_KEY . '_secret_test_2';

        $cacheItem = $client->getCache()->getItem($key);

        $cacheItem->set(new Response(['data' => ['value' => 'test']]))->expiresAfter(10);

        $client->getCache()->save($cacheItem);

        $data = $client->read('/secret/test/2')->getData();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('test', $data['value']);
        $this->assertTrue($client->getCache()->hasItem($key));
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
