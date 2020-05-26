<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class OktaAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class OktaAuthenticationStrategy extends AbstractUserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function __construct($username, $password, $methodPathSegment = 'okta')
    {
        parent::__construct($username, $password);

        $this->setMethodPathSegment($methodPathSegment);
    }
}
