<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 11/29/15
 * Time: 12:20 AM
 */

namespace Cloud\LdapBundle\Util;


use Cloud\LdapBundle\Services\LdapClient;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger,LdapClient $client, $bindDn, $bindPw, $baseDn, $services)
    {
        $this->logger=$logger;
        $this->ldap = $client;
        $this->baseDn = $baseDn;
        $this->services = $services;

        $this->ldap->bind($bindDn, $bindPw);
    }

    public function updateSchema()
    {
        $dc = preg_split("#,?dc=#", $this->baseDn);
        $dc = $dc[1];

        $this->addDcIfNotExist($this->baseDn, $dc);
        $this->addOuIfNotExist($this->baseDn, 'users');
        $this->addOuIfNotExist($this->baseDn, 'groups');

        foreach ($this->services as $service) {
            $this->addDcIfNotExist('dc=' . $service . ',' . $this->baseDn, $service);
            $this->addOuIfNotExist('dc=' . $service . ',' . $this->baseDn, 'users');
            $this->addOuIfNotExist('dc=' . $service . ',' . $this->baseDn, 'groups');
        }
    }

    public function addOuIfNotExist($dn, $name)
    {
        if (!$this->ldap->isEntityExist("ou=" . $name . "," . $dn)) {
            $data = array();
            $data['ou'] = $name;
            $data['objectClass'] = [
                'top',
                'organizationalUnit'
            ];
            $this->ldap->add("ou=" . $name . "," . $dn, $data);
            $this->logger->info("Created Ou:'"."ou=" . $name . "," . $dn."''");
        }
    }

    public function addDcIfNotExist($dn, $name)
    {
        if (!$this->ldap->isEntityExist($dn)) {
            $data = array();
            $data['dc'] = $name;
            $data['o'] = $name;
            $data['objectclass'] = array(
                'top',
                'organization',
                'dcObject'
            );
            $this->ldap->add($dn, $data);
            $this->logger->info("Created Dc:'".$dn."''");
        }
    }
}