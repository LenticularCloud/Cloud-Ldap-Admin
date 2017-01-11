<?php
namespace Cloud\LdapBundle\Security;


use Cloud\LdapBundle\Entity\AbstractUser;
use Cloud\LdapBundle\Entity\Group;
use Cloud\LdapBundle\Entity\PosixService;
use Cloud\LdapBundle\Util\LdapArrayToObjectTransformer;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Security\Core\User\LdapUserProvider as BaseLdapUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Cloud\LdapBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Cloud\LdapBundle\Schemas;

class LdapUserProvider implements UserProviderInterface
{

    protected $ldap;
    protected $baseDn;
    protected $searchDn;
    protected $searchPassword;
    protected $defaultRoles;
    protected $uidKey;
    protected $filter;
    protected $services;

    /**
     * @var Reader
     */
    protected $reader;


    public function __construct(
        LdapClientInterface $ldap,
        $baseDn,
        $searchDn = null,
        $searchPassword = null,
        array $defaultRoles = array(),
        $uidKey = 'sAMAccountName',
        $filter = '({uid_key}={username})',
        $services,
        Reader $reader
    ) {
        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->uidKey = $uidKey;
        $this->filter = $filter;

        $this->services = $services;
        $this->reader = $reader;

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
     * @return User
     */
    public function loadUserByUsername($username)
    {
        $username = $this->ldap->escape($username, '', LDAP_ESCAPE_FILTER);
        $query = str_replace('{username}', $username, str_replace('{uid_key}', $this->uidKey, $this->filter));
        $filter = array(
            'createTimestamp',
            'modifyTimestamp',
            '*',
        );

        $dn = "ou=Users,".$this->baseDn;
        try {

            $search = $this->ldap->find($dn, $query, $filter);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }

        if (!$search) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($search['count'] > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }

        $transformer = new LdapArrayToObjectTransformer($this->reader);

        $user = $transformer->reverseTransform($search[0], new User(null), $this->uidKey.'='.$username.','.$dn);

        //find security groups
        $_groups = $this->ldap->find("ou=SecurityGroups,".$this->baseDn, "(member=".$user->getDn().")");
        for ($i = 0; $i < $_groups['count']; $i++) {
            $_group = $_groups[$i];
            $name = $_group['cn'][0];
            $group = $transformer->reverseTransform($_group, new Group($name), 'cn='.$name.','.$dn);
            $user->addGroup($group);
        }

        // load services
        foreach ($this->getServices() as $serviceName => $service) {
            $class = $service['object_class'];
            $serviceObject = new $class($serviceName);
            $dn = "ou=Users,dc=".$serviceName.",".$this->baseDn;
            $search = $this->ldap->find($dn, $query, $filter);
            if ($search !== null) {
                $serviceObject = $transformer->reverseTransform($search[0], $serviceObject, $dn);
            }
            $user->addService($serviceObject);
        }

        return $user;
    }

    /**
     * get an array of all users
     *
     * @return User[]
     * @throws LdapQueryException
     */
    public function getUsers()
    {

        $users = array();
        foreach ($this->getUsernames() as $username) {
            $users[] = $this->loadUserByUsername($username);
        }

        return $users;
    }

    public function getUsernames()
    {
        $usernames = $this->ldap->getUsernames("ou=Users,".$this->baseDn);
        sort($usernames);

        return $usernames;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        $_user = $this->loadUserByUsername($user->getUsername());

        return $_user;
    }


    public function getServices()
    {
        return $this->services;
    }
}