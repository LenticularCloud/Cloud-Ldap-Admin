<?php
namespace Cloud\LdapBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Exception\UserNotFoundException;
use Cloud\LdapBundle\Exception\ConnectionErrorException;
use Cloud\LdapBundle\Exception\LdapQueryException;
use Cloud\LdapBundle\Exception\InvalidUserException;
use Cloud\LdapBundle\Entity\Service;

// @TODO ldap_free_result refactor
class LdapService
{

    /**
     *
     * @var Ressorce $ldap_resource
     */
    private $ldap_resource;

    /**
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     *
     * @var String
     */
    private $base_dn;

    /**
     *
     * @var String
     */
    private $domain;

    /**
     *
     * @var array
     */
    private $services;

    /**
     *
     * @var \Cloud\LdapBundle\Security\PasswordEncoderInterface
     */
    private $encoder;

    /**
     *
     * @param ContainerInterface $container            
     * @throws ConnectionErrorException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        $ldap_host = $this->container->getParameter('ldap_server');
        $ldap_port = $this->container->getParameter('ldap_port');
        $bind_rdn = $this->container->getParameter('ldap_bind_rdn');
        $bind_pw = $this->container->getParameter('ldap_bind_pw');
        $this->base_dn = $this->container->getParameter('ldap_base_dn');
        $this->services = $this->container->getParameter('services');
        $this->domain = $this->container->getParameter('domain');
        
        $this->ldap_resource = @ldap_connect($ldap_host, $ldap_port);
        if ($this->ldap_resource === false) {
            throw new ConnectionErrorException();
        }
        
        $result = @ldap_set_option($this->ldap_resource, LDAP_OPT_PROTOCOL_VERSION, 3);
        if ($result === false) {
            throw new ConnectionErrorException('can\'t set to ldap v3: ' . ldap_error($this->ldap_resource));
        }
        $bind = @ldap_bind($this->ldap_resource, $bind_rdn, $bind_pw);
        if ($bind === false) {
            throw new ConnectionErrorException('can\'t bind to ldap: ' . ldap_error($this->ldap_resource));
        }
        
        $this->encoder = new \Cloud\LdapBundle\Security\CryptEncoder();
    }

    /**
     * @TODO check each returnvalue and return a better exception on failuer
     */
    public function init()
    {
        $dc = current(explode('.', $this->domain));
        
        $data = array();
        $data['dc'] = $dc;
        $data['ou'] = $dc;
        $data['objectClass'] = array(
            'organizationalUnit',
            'dcObject'
        );
        ldap_add($this->ldap_resource, $this->base_dn, $data);
        
        $data = array();
        $data['ou'] = 'users';
        $data['objectClass'] = array(
            'top',
            'organizationalUnit'
        );
        ldap_add($this->ldap_resource, 'ou=users,' . $this->base_dn, $data);
        
        foreach ($this->services as $service) {
            $data = array();
            $data['dc'] = $service;
            $data['ou'] = $service;
            $data['objectClass'] = array(
                'organizationalUnit',
                'dcObject'
            );
            ldap_add($this->ldap_resource, 'dc=' . $service . ',' . $this->base_dn, $data);
            
            $data = array();
            $data['ou'] = 'users';
            $data['objectClass'] = array(
                'top',
                'organizationalUnit'
            );
            ldap_add($this->ldap_resource, 'ou=users,' . 'dc=' . $service . ',' . $this->base_dn, $data);
        }
    }

    /**
     * get an array of all users
     *
     * @return Array<User>
     * @throws LdapQueryException
     */
    public function getAllUsers()
    {
        $users = array();
        foreach ($this->getAllUsernames() as $username) {
            $users[] = $this->getUserByUsername($username);
        }
        
        return $users;
    }

    /**
     * get an array of all users
     *
     * @return Array<User>
     * @throws LdapQueryException
     */
    public function getAllUsernames()
    {
        $result = @ldap_list($this->ldap_resource, 'ou=users,' . $this->base_dn, '(uid=*)', array(
            'uid'
        ));
        
        if ($result === false) {
            throw new LdapQueryException('can not fetch userlist');
        }
        
        $info = ldap_get_entries($this->ldap_resource, $result);
        
        $users = array();
        for ($i = 0; $i < $info["count"]; $i ++) {
            $users[] = $info[$i]["uid"][0];
        }
        
        return $users;
    }

    /**
     *
     * @throws UserNotFoundException
     * @throws InvalidUserException
     */
    public function updateUser(User $user)
    {
        $errors = $this->container->get('validator')->validate($user);
        if (count($errors) > 0) {
            throw new InvalidUserException((string) $errors);
        }
        
        $result = @ldap_mod_replace($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,' . $this->base_dn, $this->userToLdapArray($user));
        if ($result === false) {
            throw new LdapQueryException('can not modify user');
        }
        foreach ($user->getServices() as $service) {
            if (in_array($service->getName(), $this->services))
                $result = @ldap_mod_replace($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->base_dn, $this->userToLdapArray($user, $service->getName()));
            if ($result === false) {
                throw new LdapQueryException('can not modify user\'s service ' . $service->getName());
            }
        }
    }

    /**
     * creates a new user
     */
    public function createUser(User $user)
    {
        $errors = $this->container->get('validator')->validate($user);
        if (count($errors) > 0) {
            throw new InvalidUserException((string) $errors);
        }
        
        $result = @ldap_add($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,' . $this->base_dn, $this->userToLdapArray($user));
        if ($result === false) {
            throw new LdapQueryException('can not add user');
        }
        foreach ($this->services as $service) {
            $result = @ldap_add($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service . ',' . $this->base_dn, $this->userToLdapArray($user, $service));
            if ($result === false) {
                throw new LdapQueryException('can not add user to service ' . $service);
            }
        }
    }

    /**
     * delete an user
     *
     * @param User $user            
     */
    public function deleteUser(User $user)
    {
        $errors = $this->container->get('validator')->validate($user);
        if (count($errors) > 0) {
            throw new InvalidUserException((string) $errors);
        }
        
        foreach ($user->getServices() as $service) {
            ldap_delete($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->base_dn);
        }
        
        ldap_delete($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,' . $this->base_dn);
    }

    /**
     * search for user and return it
     * if user is not found,return null
     *
     * @return User
     */
    public function getUserByUsername($username)
    {
        $user = new User($username);
        
        // check username to protect against inject attack
        $error = $this->container->get('validator')->validateProperty($user, 'username');
        if (count($error) > 0) {
            return null; // invalid username
        }
        
        $ri = @ldap_search($this->ldap_resource, 'uid=' . $username . ',ou=users,' . $this->base_dn, '(objectClass=inetOrgPerson)', array(
            'uid',
            'userPassword'
        ));
        if ($ri === false) {
            return null; // not found or other error
        }
        $result = ldap_first_entry($this->ldap_resource, $ri);
        if ($result === false) {
            return null; // nor found
        }
        $entity = ldap_get_attributes($this->ldap_resource, $result);
        
        for ($i = 0; $i < $entity['userPassword']['count']; $i ++) {
            $user->addPassword($this->encoder->parsePassword($entity['userPassword'][$i]));
        }
        
        ldap_free_result($ri);
        foreach ($this->services as $service_name) {
            $ri = @ldap_read($this->ldap_resource, 'uid=' . $username . ',ou=users,dc=' . $service_name . ',' . $this->base_dn, '(objectClass=inetOrgPerson)');
            if ($ri !== false) {
                $result = ldap_first_entry($this->ldap_resource, $ri);
                if ($result !== false) {
                    $entity = ldap_get_attributes($this->ldap_resource, $result);
                    $service = new Service($service_name);
                    for ($i = 0; $i < $entity['userPassword']['count']; $i ++) {
                        $password = $this->encoder->parsePassword($entity['userPassword'][$i]);
                        if (! isset($user->getPasswords()[$password->getId()])) {
                            $service->addPassword($password);
                        }
                    }
                    $user->addService($service);
                }
                
                ldap_free_result($ri);
            }
        }
        
        return $user;
    }

    /**
     * updates the users in the different services
     */
    public function updateServices()
    {
        foreach ($this->services as $service) {
            if (! $this->isEntityExist('dc=' . $service . ',' . $this->base_dn)) {
                $data = array();
                $data['dc'] = $service;
                $data['ou'] = $service;
                $data['objectClass'] = array(
                    'organizationalUnit',
                    'dcObject'
                );
                ldap_add($this->ldap_resource, 'dc=' . $service . ',' . $this->base_dn, $data);
            }
            
            if (! $this->isEntityExist('ou=users,' . 'dc=' . $service . ',' . $this->base_dn)) {
                $data = array();
                $data['ou'] = 'users';
                $data['objectClass'] = array(
                    'top',
                    'organizationalUnit'
                );
                ldap_add($this->ldap_resource, 'ou=users,' . 'dc=' . $service . ',' . $this->base_dn, $data);
            }
        }
    }

    /**
     * check if object is exist
     *
     * @param string $dn            
     */
    private function isEntityExist($dn)
    {
        $ri = @ldap_search($this->ldap_resource, $dn, '(objectClass=*)', array());
        if ($ri === false) {
            return false; // not found or other error
        }
        $result = ldap_first_entry($this->ldap_resource, $ri);
        if ($result === false) {
            return false; // nor found
        }
        
        return true;
    }

    /**
     * enables an service for an user
     *
     * @param User $user            user that is affected
     * @param string $service            service to disable
     * @throws \InvalidArgumentException
     * @throws LdapQueryException
     */
    public function enableService(User $user, $service)
    {
        if (! in_array($service, $this->services)) {
            throw new \InvalidArgumentException('service not exist');
        }
        if ($user->getService($service) != null) {
            throw new \InvalidArgumentException('service not disabled');
        }
        
        $result = @ldap_add($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service . ',' . $this->base_dn, $this->userToLdapArray($user, $service));
        if ($result === false) {
            throw new LdapQueryException('can not enable service ' . $service);
        }
    }

    /**
     * disables an service for an user
     *
     * @param User $user            user that is affected
     * @param string $service            service to disable
     * @throws \InvalidArgumentException
     * @throws LdapQueryException
     */
    public function disableService(User $user, $service)
    {
        if (! in_array($service, $this->services)) {
            throw new \InvalidArgumentException('service not exist');
        }
        if ($user->getService($service) == null) {
            throw new \InvalidArgumentException('service not enabled');
        }
        
        $result = @ldap_delete($this->ldap_resource, 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service . ',' . $this->base_dn);
        if ($result === false) {
            throw new LdapQueryException('can not disable service  ' . $service);
        }
    }

    /**
     * @TODO think about that
     */
    public function showServiceInconsistence()
    {
        // ...
        // ldap_compare parsed with saved
        throw new \BadFunctionCallException('not implemented yet');
    }

    /**
     * function to convert a user object into an array for ldap push
     *
     * @param User $user            
     * @param String $service
     *            Service name to get data
     */
    private function userToLdapArray(User $user, $service = null)
    {
        // @TODO passwordID
        $data = array();
        $data["cn"] = $user->getUsername();
        $data["uid"] = $user->getUsername();
        $data["objectClass"] = array();
        $data["objectClass"][] = "top";
        $data["objectClass"][] = "inetOrgPerson";
        $data["objectClass"][] = "posixAccount";
        $data["objectClass"][] = "shadowAccount";
        
        $data["uid"] = $user->getUsername();
        $data["homeDirectory"] = "/var/vhome/" . $user->getUsername();
        $data["givenName"] = $user->getUsername();
        $data["sn"] = $user->getUsername();
        $data["displayName"] = $user->getUsername();
        $data["mail"] = $user->getUsername() . "@" . $this->domain;
        $data['uidNumber'] = 1337; // @TODO: probably take a autoincrement id
        $data['gidNumber'] = 1337;
        $data["userPassword"] = array();
        foreach ($user->getPasswords() as $password) {
            
            if ($password->getHash() == null)
                $this->encoder->encodePassword($password);
            $data["userPassword"][] = $password->getHash();
        }
        
        if ($service !== null && $user->getService($service) != null) {
            foreach ($user->getService($service)->getPasswords() as $password) {
                if ($password->getHash() == null)
                    $this->encoder->encodePassword($password);
                $data["userPassword"][] = $password->getHash();
            }
        }
        $data["loginShell"] = "/bin/false";
        
        return $data;
    }

    /**
     *
     * @param string $service
     *            if null use
     */
    private function getBaseDN($service = null)
    {
        return "ou=Users" + ($service == null ? "" : "DN=" + $service) + $this->base_dn;
    }

    /**
     * close current ldap connection
     */
    public function close()
    {
        ldap_close($this->ldap_resource);
    }

    /**
     */
    public function getServices()
    {
        return $this->services;
    }
}
