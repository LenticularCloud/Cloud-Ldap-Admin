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
use InvalidArgumentException;

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
        
        if (true !== @ldap_bind($this->connection, $dn, $password)) {
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
            return null;
        }
        $infos = ldap_get_entries($this->connection, $search);
        if (0 === $infos['count']) {
            return null;
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
        if (true != @ldap_mod_replace($this->connection, $dn, $entity)) {
            if(ldap_errno($this->connection)) { //Cannot modify object class
                $this->delete($dn);
                $this->add($dn,$entity);
            }else {
                throw new LdapException(ldap_error($this->connection));
            }
        }
    }
    
    public function delete($dn)
    {
        if (true != @ldap_delete($this->connection, $dn)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    public function add($dn,array $entity)
    {
        if (true != @ldap_add($this->connection, $dn, $entity)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * check if object is exist
     *
     * @param string $dn            
     */
    public function isEntityExist($dn)
    {
        $ri = @ldap_search($this->connection, $dn, '(objectClass=*)', array());
        if ($ri === false) {
            return false; // not found or other error
        }
        $result = ldap_first_entry($this->connection, $ri);
        if ($result === false) {
            return false; // not found
        }
        
        return true;
    }

    /**
     * get an array of all users
     *
     * @return Array<User>
     * @throws LdapQueryException
     */
    public function getUsernames($dn)
    {
        $result = @ldap_list($this->connection,$dn, '(uid=*)', array(
            'uid'
        ));
        
        if ($result === false) {
            throw new LdapException(ldap_error($this->connection)." DN:".$dn);
        }
        
        $info = ldap_get_entries($this->connection, $result);
        
        $users = array();
        for ($i = 0; $i < $info["count"]; $i ++) {
            $users[] = $info[$i]["uid"][0];
        }
        
        return $users;
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
}
