<?php

use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\TokenAuthenticationStrategy;
use Vault\Client;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\Uri;

class TokenAuthenticationStrategyTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Vault\Exceptions\RuntimeException
     */
    public function testCanAuthenticate(): void
    {
        $client = new Client(
            new Uri('http://127.0.0.1:8200'),
            new \AlexTartan\GuzzlePsr18Adapter\Client(),
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setAuthenticationStrategy(new TokenAuthenticationStrategy('db02de05-fa39-4855-059b-67221c5c2f63'))
            ->setLogger(new NullLogger());

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
    }

    protected function setUp()
    {
        \VCR\VCR::turnOn();

        \VCR\VCR::insertCassette('authentication-strategies/token');

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
