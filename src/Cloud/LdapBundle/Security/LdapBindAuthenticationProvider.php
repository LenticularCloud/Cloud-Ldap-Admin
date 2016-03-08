<?php
namespace Cloud\LdapBundle\Security;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Security\Core\Authentication\Provider\LdapBindAuthenticationProvider as BaseLdapBindAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class LdapBindAuthenticationProvider extends UserAuthenticationProvider
{
    private $userProvider;
    private $ldap;
    private $dnString;

    /**
     * Constructor.
     *
     * @param UserProviderInterface $userProvider               A UserProvider
     * @param UserCheckerInterface  $userChecker                A UserChecker
     * @param string                $providerKey                The provider key
     * @param LdapClientInterface   $ldap                       An Ldap client
     * @param string                $dnString                   A string used to create the bind DN
     * @param bool                  $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, LdapClientInterface $ldap, $dnString = '{username}', $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);

        $this->userProvider = $userProvider;
        $this->ldap = $ldap;
        $this->dnString = $dnString;
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        if ('NONE_PROVIDED' === $username) {
            throw new UsernameNotFoundException('Username can not be null');
        }

        return $this->userProvider->loadUserByUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $username = $token->getCn();
        $password = $token->getCredentials();

        try {
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_DN);
            $dn = str_replace('{username}', $username, $this->dnString);
            dump($dn, $password);
            $this->ldap->bind($dn, $password);
        } catch (ConnectionException $e) {
            throw new BadCredentialsException('The presented password is invalid.');
        }
    }
}