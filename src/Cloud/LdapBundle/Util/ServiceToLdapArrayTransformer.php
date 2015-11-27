<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Services\LdapClient;
use Cloud\LdapBundle\Entity\User;
use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Entity\Service;

class ServiceToLdapArrayTransformer implements DataTransformerInterface
{
    protected $serviceName;
    
    public function __construct($serviceName)
    {
        $this->serviceName=$serviceName;
    }
    
    public function transform($service)
    {
        if(! $service instanceof Service ) {
            throw new InvalidArgumentException();
        }
        
        $data = array();
        $data["cn"] = $service->getUser()->getUsername();
        $data["uid"] = $service->getUser()->getUsername();
        $data["objectClass"] = array();
        $data["objectClass"][] = "top";
        $data["objectClass"][] = "inetOrgPerson";
        $data["objectClass"][] = "posixAccount";
        $data["objectClass"][] = "shadowAccount";
        
        $data["uid"] = $service->getUser()->getUsername();
        $data["homeDirectory"] = "/var/vhome/" . $service->getUser()->getUsername();
        $data["givenName"] = $service->getUser()->getUsername();
        $data["sn"] = $service->getUser()->getUsername();
        $data["displayName"] = $service->getUser()->getUsername();
        $data["mail"] = $service->getUser()->getUsername() . "@" ."test.com";// $service->getUser()->getDomain();
        $data['uidNumber'] = 1337; // @TODO: probably take a autoincrement id
        $data['gidNumber'] = 1337;
        $data["userPassword"] = array();
        foreach ($service->getPasswords() as $password) {
            
            if ($password->getPasswordPlain() !== null){
                $service->getEncoder()->encodePassword($password);
            }
            $data["userPassword"][] = $password->getHash();
        }
        if($service->isMasterPasswordEnabled()) {
            foreach($service->getUser()->getPasswords() as $password) {
                if ($password->getPasswordPlain() !== null){
                    $service->getEncoder()->encodePassword($password);
                }
                $data["userPassword"][] = $password->getHash();
            }
        }

        if(count( $data["userPassword"])==0) {
            unset($data["userPassword"]);
        }
        
        $data["loginShell"] = "/bin/false";
        
        return $data;
    }

    public function reverseTransform($ldapArray)
    {
        dump($ldapArray);
        $service=new Service($this->serviceName);
        
        if($ldapArray===null) {
            $service->setEnabled(false);
            return $service;
        }
        
        $service->setEnabled(true);
        
        $passwords=isset($ldapArray['userpassword'])?$ldapArray['userpassword']:array('count'=>0);
        dump($passwords);
        for ($i = 0; $i < $passwords['count']; $i ++) {
            dump($ldapArray['userpassword'][$i]);
            $password=$service->getEncoder()->parsePassword($ldapArray['userpassword'][$i]);
            if($password===null) {
                continue;
            }
            if($password->isMasterPassword()) {
                $service->setMasterPasswordEnabled(true);
            }else {
                $service->addPassword($password);
            }
        }
        dump($service);
        
        return $service;
    }
}
