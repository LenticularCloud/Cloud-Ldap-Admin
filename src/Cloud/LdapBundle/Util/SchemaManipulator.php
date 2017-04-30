<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 11/29/15
 * Time: 12:20 AM
 */

namespace Cloud\LdapBundle\Util;


use Cloud\LdapBundle\Security\LdapUserProvider;
use Cloud\LdapBundle\Services\LdapClient;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Cloud\LdapBundle\Schemas;

class SchemaManipulator
{
    /**
     * @var string
     */
    private $baseDn;
    /**
     * @var array
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

    /**
     * @var LdapUserProvider
     */
    private $userProvider;

    /**
     * @var UserManipulator
     */
    private $userManipulator;

    /**
     * @var string
     */
    private $domain;

    public function __construct(LoggerInterface $logger, LdapUserProvider $userProvider, UserManipulator $userManipulator, LdapClient $client, $bindDn, $bindPw, $baseDn, $services,$domain)
    {
        $this->logger = $logger;
        $this->userProvider = $userProvider;
        $this->userManipulator = $userManipulator;
        $this->ldap = $client;
        $this->baseDn = $baseDn;
        $this->services = $services;
        $this->domain = $domain;

        $this->ldap->bind($bindDn, $bindPw);
    }

    public function updateSchema()
    {
        $dc = preg_split("#,?dc=#", $this->baseDn);
        $dc = $dc[1];

        $this->addDcIfNotExist($this->baseDn, $dc);
        $this->addOuIfNotExist($this->baseDn, 'users');
        $this->addOuIfNotExist($this->baseDn, 'groups');
        $this->addOuIfNotExist($this->baseDn, 'SecurityGroups');

        foreach ($this->services as $serviceName => $service) {
            $this->addDcIfNotExist('dc=' . $serviceName . ',' . $this->baseDn, $serviceName);
            $this->addOuIfNotExist('dc=' . $serviceName . ',' . $this->baseDn, 'users');
            $this->addOuIfNotExist('dc=' . $serviceName . ',' . $this->baseDn, 'groups');
        }

        foreach ($this->userProvider->getUsernames() as $username) {
            $user = $this->userProvider->loadUserByUsername($username);

            $user->setEmail($user->getUsername().'@'.$this->domain);

            if ($user->getObject(Schemas\LenticularUser::class) === null) {
                $user->addObject(Schemas\LenticularUser::class);
                $user->addRole('ROLE_USER');
            }

            if ($user->getObject(Schemas\PosixAccount::class) !== null) {
                $user->removeObject(Schemas\PosixAccount::class,
                    ['uidnumber', 'gidnumber', 'homedirectory', 'gecos', 'loginshell']);
            }

            foreach ($user->getServices() as $service) {
                if ($service->isEnabled()) {
                    foreach ($service->getObjectClasses() as $objectClass) {
                        if ($service->getObject($objectClass) === null) {
                            $service->addObject($objectClass);
                        }
                    }

                    if ($service->getObject(Schemas\PosixAccount::class) !== null && in_array(Schemas\PosixAccount::class, $service->getObjectClasses())) {
                        $service->removeObject(Schemas\PosixAccount::class,
                            ['uidnumber', 'gidnumber', 'homedirectory', 'gecos', 'loginshell']);
                    }

                    $attr=$service->getAttributes()->get('mail');
                    if($attr!==null) {
                        $attr->set($user->getEmail());
                    }
                }
            }

            $this->userManipulator->update($user);
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
            $this->logger->info("Created Ou:'" . "ou=" . $name . "," . $dn . "''");
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
            $this->logger->info("Created Dc:'" . $dn . "''");
        }
    }
}