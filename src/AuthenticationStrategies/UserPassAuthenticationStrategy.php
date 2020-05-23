<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class UserPassAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class UserPassAuthenticationStrategy extends AbstractUserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function __construct($username, $password, $methodPathSegment = 'userpass')
    {
        parent::__construct($username, $password);

        $this->setMethodPathSegment($methodPathSegment);
    }
}
