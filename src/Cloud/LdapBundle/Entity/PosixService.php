<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use \InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Schemas;

class PosixService extends Service
{


    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function getObjectClasses()
    {
        $classes = parent::getObjectClasses();
        $classes['posixaccount'] = Schemas\PosixAccount::class;
        $classes['ldappublickey'] = Schemas\LdapPublicKey::class;

        return $classes;
    }


    protected function serviceEnabled()
    {
        parent::serviceEnabled();
        $username = $this->getUser()->getUsername();
        //generate constant uid @TODO salt
        $uid = hexdec(substr(hash("sha256",$username),-4)) %40000+1000;
        $this->setUid($uid);
        $this->setGid($uid);
        $this->setHomeDirector('/home/'.$username);
        $this->setLoginShell('/bin/bash');
        $this->getObject(Schemas\PosixAccount::class)->setCn($username);
    }

    public function getUid()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getUidNumber();
    }

    public function setUid($uid)
    {
        
        return $this->getObject(Schemas\PosixAccount::class)->setUidNumber($uid);
    }

    public function getGid()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getGidNumber();
    }

    public function setGid($gid)
    {
        return $this->getObject(Schemas\PosixAccount::class)->setGidNumber($gid);
    }

    public function getHomeDirector()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getHomeDirectory();
    }

    public function setHomeDirector($homeDirectory)
    {
        return $this->getObject(Schemas\PosixAccount::class)->setHomeDirectory($homeDirectory);
    }

    public function getLoginShell()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getLoginShell();
    }

    public function setLoginShell($loginShell)
    {
        return $this->getObject(Schemas\PosixAccount::class)->setLoginShell($loginShell);
    }

    public function getSshPublicKey()
    {
        $object = $this->getObject(Schemas\LdapPublicKey::class);
        if ($object !== null) {
            return $object->getSshPublicKeys();
        }

        return null;
    }

    public function addSshPublicKey($sshPublicKey)
    {
        return $this->getObject(Schemas\LdapPublicKey::class)->addSshPublicKey($sshPublicKey);
    }

    public function removeSshPublicKey($sshPublicKey)
    {
        return $this->getObject(Schemas\LdapPublicKey::class)->removeSshPublicKey($sshPublicKey);
    }
}
