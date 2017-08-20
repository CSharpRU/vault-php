<?php

use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\Client;
use VaultTransports\Guzzle6Transport;

class AppRoleAuthenticationStrategyTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCanAuthenticate()
    {
        $client = (new Client(new Guzzle6Transport()))
            ->setAuthenticationStrategy(
                new AppRoleAuthenticationStrategy(
                    'db02de05-fa39-4855-059b-67221c5c2f63',
                    '6a174c20-f6de-a53c-74d2-6018fcceff64'
                )
            )
            ->setLogger(new NullLogger());

        $this->assertEquals($client->getAuthenticationStrategy()->getClient(), $client);
        $this->assertTrue($client->authenticate());
        $this->assertNotEmpty($client->getToken());
        $this->assertNotEmpty($client->getToken()->getAuth()->getLeaseDuration());
        $this->assertNotEmpty($client->getToken()->getAuth()->isRenewable());

        return $client;
    }

    protected function setUp()
    {
        \VCR\VCR::turnOn();

        \VCR\VCR::insertCassette('authentication-strategies/app-role');

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
