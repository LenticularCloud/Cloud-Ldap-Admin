<?php
namespace Cloud\LdapBundle\Security;


use Symfony\Component\Security\Core\User\LdapUserProvider as BaseLdapUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;

class LdapUserProvider extends BaseLdapUserProvider
{

    protected $ldap;
    protected $baseDN;
    protected $searchDN;
    protected $searchPassword;
    protected $defaultRoles;
    protected $uidKey;
    protected $filter;
    
    
    public function __construct(LdapClientInterface $ldap, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})')
    {
        $this->ldap = $ldap;
        parent::__construct($ldap, $baseDn, $searchDn, $searchPassword , $defaultRoles, $uidKey , $filter);
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->uidKey = $uidKey;
        $this->filter = $filter;
    }
    
    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $userClass = 'Symfony\Component\Security\Core\User\User';
    
        return $userClass === $class || is_subclass_of($class, $userClass);
    }
    
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LdapClientInterface::LDAP_ESCAPE_FILTER);
            $query = str_replace('{username}', $username, str_replace('{uid_key}', $this->uidKey, $this->filter));
            $search = $this->ldap->find($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }
        
        if (!$search) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
        
        if ($search['count'] > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }
        
        $user = $search[0];
        
        return $this->loadUser($username, $user);
    }
}