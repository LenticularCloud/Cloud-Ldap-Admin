<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 11/29/15
 * Time: 12:20 AM
 */

namespace Cloud\LdapBundle\Util;


use Cloud\LdapBundle\Services\LdapClient;

class Ldap
{
    /**
     * @var string
     */
    private $baseDn;
    /**
     * @var string
     */
    private $services;
    /**
     * @var LdapClient
     */
    private $ldap;

    public function __construct(LdapClient $client,$baseDn,$services)
    {
        $this->ldap=$client;
        $this->baseDn=$baseDn;
        $this->services=$services;
    }

    public function updateSchema() {
        $this->addOuIfNotExist('ou=users,'.$this->baseDn);

        foreach ($this->services as $service) {
            $this->addOuIfNotExist('dc=' . $service . ',' . $this->baseDn);
            $this->addDcIfNotExist('ou=users,dc=' . $service . ',' . $this->baseDn);
        }
    }

    public function addOuIfNotExist($dn)
    {
        if ($this->ldap->find($dn, '') === null) {
            $data = array();
            $data['ou'] = 'users';
            $data['objectClass'] = array(
                'organizationalUnit',
                'top'
            );
        }
        //$this->ldap->add($dn, $data);
    }

    public function addDcIfNotExist($dn,$dc=null)
    {
        if ($this->ldap->find($dn, '') === null) {
            $data = array();
            $data['dc'] = 'users';
            $data['objectClass'] = array(
                'organizationalUnit',
                'dcObject'
            );
            //$this->ldap->add($dn, $data);
        }
    }
}