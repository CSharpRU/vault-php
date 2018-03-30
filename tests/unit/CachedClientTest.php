<?php

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;
use Vault\CachedClient;
use Vault\ResponseModels\Response;
use VaultTransports\Guzzle6Transport;

class CachedClientTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testReadCache()
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
     */
    private function getAuthenticatedClient()
    {
        $client = (new CachedClient(new Guzzle6Transport()))
            ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
            ->setLogger(new NullLogger());

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
        $this->assertNotEmpty($client->getToken()->getAuth()->getLeaseDuration());
        $this->assertNotEmpty($client->getToken()->getAuth()->isRenewable());

        return $client;
    }

    public function testReadCacheKeyAlreadyInCache()
    {
        $client = $this->getAuthenticatedClient()->enableReadCache()->setCache(new ArrayCachePool());
        $key = CachedClient::READ_CACHE_KEY . '_secret_test_2';

        $client->getCache()->save((new CacheItem($key))->set(new Response(['data' => ['value' => 'test']]))->expiresAfter(10));

        $data = $client->read('/secret/test/2')->getData();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('test', $data['value']);
        $this->assertTrue($client->getCache()->hasItem($key));
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
