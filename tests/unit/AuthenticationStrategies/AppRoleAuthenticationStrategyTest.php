<?php

use Codeception\Test\Unit;
use Psr\Http\Client\ClientExceptionInterface;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\Client;
use VCR\VCR;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;

class AppRoleAuthenticationStrategyTest extends Unit
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
    public function testCanAuthenticate(): void
    {
        $client = new Client(
            new Uri('http://127.0.0.1:8200'),
            new \AlexTartan\GuzzlePsr18Adapter\Client(),
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setAuthenticationStrategy(
            new AppRoleAuthenticationStrategy(
                'db02de05-fa39-4855-059b-67221c5c2f63',
                '6a174c20-f6de-a53c-74d2-6018fcceff64'
            )
        );

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
        $this->assertNotEmpty($client->getToken()->getAuth()->getLeaseDuration());
        $this->assertNotEmpty($client->getToken()->getAuth()->isRenewable());
    }

    protected function setUp(): void
    {
        VCR::turnOn();

        VCR::insertCassette('authentication-strategies/app-role');

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
