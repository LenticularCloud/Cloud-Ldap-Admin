<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Services\LdapClient;
use Cloud\LdapBundle\Entity\User;
use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;
use Cloud\LdapBundle\Security\CryptEncoder;

class GroupToLdapArrayTransformer implements DataTransformerInterface
{
    /**
     * 
     * @var CryptEncoder
     */
    protected $encoder;
    
    public function __construct()
    {
        $this->encoder=new CryptEncoder();
    }
    
    public function transform($user)
    {
        if(! $user instanceof User ) {
            throw new InvalidArgumentException();
        }
        
        $data = array();
        $data["cn"] = $user->getName();
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
        //$data["mail"] = $user->getUsername() . "@" . $this->domain;
        $data['uidNumber'] = 1337; // @TODO: probably take a autoincrement id
        $data['gidNumber'] = 1337;
        $data["loginShell"] = "/bin/false";
        $data["userPassword"] = array();
        foreach ($user->getPasswords() as $password) {
        
            if ($password->getPasswordPlain() !== null){
                $this->encoder->encodePassword($password);
            }
            $data["userPassword"][] = $password->getHash();
        }
        
        return $data;
    }

    public function reverseTransform($ldapArray)
    {
        $user=new Group($ldapArray['gid'][0]);

        
        return $user;
    }
}
