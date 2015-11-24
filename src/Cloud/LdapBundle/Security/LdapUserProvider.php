<?php
namespace Cloud\LdapBundle\Security;


use Symfony\Component\Security\Core\User\LdapUserProvider as BaseLdapUserProvider;

class LdapUserProvider extends BaseLdapUserProvider
{

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $userClass = 'Symfony\Component\Security\Core\User\User';
    
        return $userClass === $class || is_subclass_of($class, $userClass);
    }
}