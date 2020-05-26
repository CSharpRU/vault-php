<?php

namespace Vault\AuthenticationStrategies;

/**
 * Class LdapAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class LdapAuthenticationStrategy extends AbstractPathAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $methodPathSegment = 'ldap';
}
