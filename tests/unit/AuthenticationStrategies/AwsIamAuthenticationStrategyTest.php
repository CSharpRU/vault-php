<?php

use Aws\Sts\StsClient;
use Codeception\Test\Unit;
use Psr\Http\Client\ClientExceptionInterface;
use Vault\AuthenticationStrategies\AwsIamAuthenticationStrategy;
use Vault\Client;
use VCR\VCR;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;

class AwsIamAuthenticationStrategyTest extends Unit
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

        $stsClient = new StsClient([
            'region' => 'eu-central-1',
            'version' => 'latest',
            'sts_regional_endpoints' => 'regional'
        ]);
        // These middlewares would break the test, due to some dynamic headers
        $stsClient->getHandlerList()->remove('invocation-id');
        $stsClient->getHandlerList()->remove('signer');

        $client->setAuthenticationStrategy(
            new AwsIamAuthenticationStrategy(
                'dev-role',
                'eu-central-1',
                'localhost',
                $stsClient
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
        VCR::configure()->setMode(VCR::MODE_ONCE);

        VCR::insertCassette('authentication-strategies/aws-iam');

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
