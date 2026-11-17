<?php

use PHPUnit\Framework\TestCase;
use Vault\Client;
use Vault\Models\Token;

class TokenExpiryTest extends TestCase
{
    private function isExpired(array $values): bool
    {
        $class = new ReflectionClass(Client::class);
        $client = $class->newInstanceWithoutConstructor();
        $method = $class->getMethod('isTokenExpired');
        $method->setAccessible(true);

        return $method->invoke($client, new Token($values));
    }

    public function testRenewedTokenRemainsValidBeyondOriginalLifetime(): void
    {
        $this->assertFalse($this->isExpired([
            'creationTime' => time() - 30 * 86400,
            'creationTtl' => 172800,
            'expireTime' => gmdate('c', time() + 86400),
        ]));
    }

    public function testCurrentExpiryOverridesOriginalLifetime(): void
    {
        $this->assertTrue($this->isExpired([
            'creationTime' => time(),
            'creationTtl' => 172800,
            'expireTime' => gmdate('c', time() - 60),
        ]));
    }

    public function testMalformedExpiryIsRejected(): void
    {
        $this->assertTrue($this->isExpired(['expireTime' => 'not-a-timestamp']));
    }

    public function testLegacyAndNonExpiringTokensRetainExistingBehavior(): void
    {
        $this->assertFalse($this->isExpired(['creationTime' => 1, 'creationTtl' => 0]));
        $this->assertTrue($this->isExpired(['creationTime' => 1, 'creationTtl' => 60]));
        $this->assertFalse($this->isExpired(['creationTime' => time(), 'creationTtl' => 60]));
    }
}
