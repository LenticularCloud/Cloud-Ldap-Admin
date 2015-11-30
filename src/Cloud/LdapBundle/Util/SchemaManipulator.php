<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 11/29/15
 * Time: 12:20 AM
 */

namespace Cloud\LdapBundle\Util;


use Cloud\LdapBundle\Services\LdapClient;

class SchemaManipulator
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

    public function __construct(LdapClient $client,$bindDn,$bindPw,$baseDn,$services)
    {
        $this->ldap=$client;
        $this->baseDn=$baseDn;
        $this->services=$services;

        $this->ldap->bind($bindDn,$bindPw);
    }

    public function updateSchema() {
        $this->addOuIfNotExist('ou=users,'.$this->baseDn);

        foreach ($this->getServices() as $service) {
            $this->addDcIfNotExist('dc=' . $service . ',' . $this->baseDn,$service);
            $this->addOuIfNotExist('ou=users,dc=' . $service . ',' . $this->baseDn);
        }
    }

    public function addOuIfNotExist($dn)
    {
        if (!$this->ldap->isEntityExist($dn)) {
            $data = array();
            $data['ou'] = 'users';
            $data['objectClass'] = array(
                'organizationalUnit'
            );
            $this->ldap->add($dn, $data);
        }
    }

    public function addDcIfNotExist($dn,$dc)
    {
        if (!$this->ldap->isEntityExist($dn)) {
            $data = array();
            $data['ou'] = $dc;
            $data['dc'] = $dc;
            $data['objectClass'] = array(
                'organizationalUnit',
                'dcObject'
            );
            $this->ldap->add($dn, $data);
        }
    }
}