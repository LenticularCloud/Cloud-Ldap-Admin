<?php
namespace Cloud\LdapBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\LdapBindAuthenticationProvider as BaseLdapBindAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 
 * @author norbert
 *
 */
class LdapBindAuthenticationProvider extends BaseLdapBindAuthenticationProvider
{
    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $password = $token->getCredentials();
        if($password===null || $password === '') {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        return parent::checkAuthentication($user,$token);
    }
}