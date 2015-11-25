<?php
namespace Cloud\LdapBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Ldap\LdapClientInterface;
use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\Service;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LdapClient implements LdapClientInterface
{

    private $host;

    private $port;

    private $version;

    private $useSsl;

    private $useStartTls;

    private $optReferrals;

    private $connection;

    private $charmaps;

    /**
     * Constructor.
     *
     * @param string $host            
     * @param int $port            
     * @param int $version            
     * @param bool $useSsl            
     * @param bool $useStartTls            
     * @param bool $optReferrals            
     */
    public function __construct($host = null, $port = 389, $version = 3, $useSsl = false, $useStartTls = false, $optReferrals = false)
    {
        if (! extension_loaded('ldap')) {
            throw new LdapException('The ldap module is needed.');
        }
        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
        $this->useSsl = (bool) $useSsl;
        $this->useStartTls = (bool) $useStartTls;
        $this->optReferrals = (bool) $optReferrals;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function bind($dn = null, $password = null)
    {
        if (! $this->connection) {
            $this->connect();
        }
        if (false === @ldap_bind($this->connection, $dn, $password)) {
            throw new ConnectionException(ldap_error($this->connection));
        }
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function find($dn, $query, $filter = '*')
    {
        if (! is_array($filter)) {
            $filter = array(
                $filter
            );
        }
        $search = @ldap_search($this->connection, $dn, $query, $filter);
        if($search===false) {
            return;
        }
        $infos = ldap_get_entries($this->connection, $search);
        if (0 === $infos['count']) {
            return;
        }
        return $infos;
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function escape($subject, $ignore = '', $flags = 0)
    {
        if (function_exists('ldap_escape')) {
            return ldap_escape($subject, $ignore, $flags);
        }
        return $this->doEscape($subject, $ignore, $flags);
    }

    private function connect()
    {
        if (! $this->connection) {
            $host = $this->host;
            if ($this->useSsl) {
                $host = 'ldaps://' . $host;
            }
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->version);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, $this->optReferrals);
            $this->connection = ldap_connect($host, $this->port);
            if ($this->useStartTls) {
                ldap_start_tls($this->connection);
            }
        }
    }

    private function disconnect()
    {
        if ($this->connection && is_resource($this->connection)) {
            ldap_unbind($this->connection);
        }
        $this->connection = null;
    }

    /**
     * Stub implementation of the {@link ldap_escape()} function of the ldap
     * extension.
     *
     * Escape strings for safe use in LDAP filters and DNs.
     *
     * @author Chris Wright <ldapi@daverandom.com>
     *        
     * @param string $subject            
     * @param string $ignore            
     * @param int $flags            
     *
     * @return string
     *
     * @see http://stackoverflow.com/a/8561604
     */
    private function doEscape($subject, $ignore = '', $flags = 0)
    {
        $charMaps = $this->getCharmaps();
        // Create the base char map to escape
        $flags = (int) $flags;
        $charMap = array();
        if ($flags & self::LDAP_ESCAPE_FILTER) {
            $charMap += $charMaps[self::LDAP_ESCAPE_FILTER];
        }
        if ($flags & self::LDAP_ESCAPE_DN) {
            $charMap += $charMaps[self::LDAP_ESCAPE_DN];
        }
        if (! $charMap) {
            $charMap = $charMaps[0];
        }
        // Remove any chars to ignore from the list
        $ignore = (string) $ignore;
        for ($i = 0, $l = strlen($ignore); $i < $l; ++ $i) {
            unset($charMap[$ignore[$i]]);
        }
        // Do the main replacement
        $result = strtr($subject, $charMap);
        // Encode leading/trailing spaces if LDAP_ESCAPE_DN is passed
        if ($flags & self::LDAP_ESCAPE_DN) {
            if ($result[0] === ' ') {
                $result = '\\20' . substr($result, 1);
            }
            if ($result[strlen($result) - 1] === ' ') {
                $result = substr($result, 0, - 1) . '\\20';
            }
        }
        return $result;
    }

    private function getCharmaps()
    {
        if (null !== $this->charmaps) {
            return $this->charmaps;
        }
        $charMaps = array(
            self::LDAP_ESCAPE_FILTER => array('\\', '*', '(', ')', "\x00"),
            self::LDAP_ESCAPE_DN => array('\\', ',', '=', '+', '<', '>', ';', '"', '#'),
        );
        $charMaps[0] = array();
        for ($i = 0; $i < 256; ++ $i) {
            $charMaps[0][chr($i)] = sprintf('\\%02x', $i);
        }
        for ($i = 0, $l = count($charMaps[self::LDAP_ESCAPE_FILTER]); $i < $l; ++ $i) {
            $chr = $charMaps[self::LDAP_ESCAPE_FILTER][$i];
            unset($charMaps[self::LDAP_ESCAPE_FILTER][$i]);
            $charMaps[self::LDAP_ESCAPE_FILTER][$chr] = $charMaps[0][$chr];
        }
        for ($i = 0, $l = count($charMaps[self::LDAP_ESCAPE_DN]); $i < $l; ++ $i) {
            $chr = $charMaps[self::LDAP_ESCAPE_DN][$i];
            unset($charMaps[self::LDAP_ESCAPE_DN][$i]);
            $charMaps[self::LDAP_ESCAPE_DN][$chr] = $charMaps[0][$chr];
        }
        $this->charmaps = $charMaps;
        return $this->charmaps;
    }

    /**
     *
     * @var array
     */
    private $services;
    
    public function replace($dn,array $entity)
    {
        dump($dn,$entity);
        $result = @ldap_mod_replace($this->ldap_resource, $dn, $entity);
        if ($result === false) {
            throw new LdapException('can not modify user');
        }
    }
    
    //------------------------old part -----------------------------//

    /**
     *
     * @param ContainerInterface $container            
     * @throws ConnectionErrorException
     * /
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
     * 
     * @param boolean $dirtRun
     *            boolean to make a dirt run to show changes
     * @return
     *
     */
    public function updateServices($dirtRun = false)
    {
        if ($this->isEntityExist($this->base_dn)) {
            $dc = current(explode('.', $this->domain));
            
            $data = array();
            $data['dc'] = $dc;
            $data['ou'] = $dc;
            $data['objectClass'] = array(
                'organizationalUnit',
                'dcObject'
            );
            ldap_add($this->ldap_resource, $this->base_dn, $data);
        }
        if ($this->isEntityExist('ou=users,' . $this->base_dn)) {
            $data = array();
            $data['ou'] = 'users';
            $data['objectClass'] = array(
                'top',
                'organizationalUnit'
            );
            ldap_add($this->ldap_resource, 'ou=users,' . $this->base_dn, $data);
        }
        
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
     * @param User $user
     *            user that is affected
     * @param string $service
     *            service to disable
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
     * @param User $user
     *            user that is affected
     * @param string $service
     *            service to disable
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
