<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class LdapAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class LdapAuthenticationStrategy extends AbstractUserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function __construct($username, $password, $methodPathSegment = 'ldap')
    {
        parent::__construct($username, $password);

        $this->setMethodPathSegment($methodPathSegment);
    }
}
