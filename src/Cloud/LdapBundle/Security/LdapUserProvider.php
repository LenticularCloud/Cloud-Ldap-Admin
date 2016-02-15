<?php
namespace Cloud\LdapBundle\Security;


use Symfony\Component\Security\Core\User\LdapUserProvider as BaseLdapUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Cloud\LdapBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Cloud\LdapBundle\Util\UserToLdapArrayTransformer;
use Cloud\LdapBundle\Util\ServiceToLdapArrayTransformer;

class LdapUserProvider implements UserProviderInterface
{

    protected $ldap;
    protected $baseDN;
    protected $searchDN;
    protected $searchPassword;
    protected $defaultRoles;
    protected $uidKey;
    protected $filter;
    protected $services;
    
    
    public function __construct(LdapClientInterface $ldap, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})',array $services)
    {
        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->uidKey = $uidKey;
        $this->filter = $filter;
        $this->services = $services;

        $this->ldap->bind($this->searchDn, $this->searchPassword);
        $this->ldap->bind($this->searchDn, $this->searchPassword);
    }
    
    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {        
        $userClass = 'Symfony\Component\Security\Core\User\UserInterface';
    
        return $userClass === $class || is_subclass_of($class, $userClass);
    }
    
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $username = $this->ldap->escape($username, '', LDAP_ESCAPE_FILTER);
        $query = str_replace('{username}', $username, str_replace('{uid_key}', $this->uidKey, $this->filter));
        
        try {
            
            $search = $this->ldap->find("ou=Users,".$this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }
        
        if (!$search) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
        
        if ($search['count'] > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }
        
        $userTransformer=new UserToLdapArrayTransformer();

        $user = $userTransformer->reverseTransform($search[0]);
        
        foreach ($this->defaultRoles as $defaultRole) {
            $user->addRoles($defaultRole);
        }
        
        foreach($this->services as $service) {
            $serviceTransformer=new ServiceToLdapArrayTransformer($service);
            $search = $this->ldap->find("ou=Users,dc=".$service.",".$this->baseDn, $query);
            $service=$serviceTransformer->reverseTransform($search[0]);
            $user->addService($service);
        }
        
        
        return $user;
    }

    /**
     * get an array of all users
     *
     * @return Array<User>
     * @throws LdapQueryException
     */
    public function getUsers()
    {
        
        $users = array();
        foreach ($this->ldap->getAllUsernames() as $username) {
            $users[] = $this->loadUserByUsername($username);
        }
        
        return $users;
    }
    
    public function getUsernames() {
        return $this->ldap->getUsernames("ou=Users,".$this->baseDn);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }
    
    
    public function getServiceNames() {
        return $this->services;
    }
}