<?php
namespace Cloud\LdapBundle\Schemas;


use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'Abstraction of an account with POSIX attributes'
 * SUP top AUXILIARY
 * MUST ( cn $ uid $ uidNumber $ gidNumber $ homeDirectory )
 * MAY ( userPassword $ loginShell $ gecos $ description ) )
 * @LDAP\Schema()
 */
class PosixAccount
{

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $cn;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="number")
     */
    private $uidNumber;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="number")
     */
    private $gidNumber;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $homeDirectory;

    /**
     * @var ArrayCollection
     * @LDAP\Attribute(name="userPassword",type="array")
     */
    private $userPasswords;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $loginShell;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $gecos;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $description;


    public function __construct()
    {
        $this->cn = new Attribute();
        $this->uid = new Attribute();
        $this->uidNumber = new Attribute();
        $this->gidNumber = new Attribute();
        $this->homeDirectory = new Attribute();
        $this->userPasswords = new ArrayCollection();
        $this->loginShell = new Attribute();
        $this->gecos = new Attribute();
        $this->description = new Attribute();
    }


    /**
     * @return mixed
     */
    public function getCn()
    {
        return $this->cn->get();
    }

    /**
     * @param mixed $cn
     * @return PosixAccount
     */
    public function setCn($cn)
    {
        $this->cn->set($cn);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid->get();
    }

    /**
     * @param mixed $uid
     * @return PosixAccount
     */
    public function setUid($uid)
    {
        $this->uid->set($uid);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description->get();
    }

    /**
     * @param mixed $description
     * @return PosixAccount
     */
    public function setDescription($description)
    {
        $this->description->set($description);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUidNumber()
    {
        return $this->uidNumber->get();
    }

    /**
     * @param mixed $uidNumber
     * @return PosixAccount
     */
    public function setUidNumber($uidNumber)
    {
        $this->uidNumber->set($uidNumber);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGidNumber()
    {
        return $this->gidNumber->get();
    }

    /**
     * @param mixed $gidNumber
     * @return PosixAccount
     */
    public function setGidNumber($gidNumber)
    {
        $this->gidNumber->set($gidNumber);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHomeDirectory()
    {
        return $this->homeDirectory->get();
    }

    /**
     * @param mixed $homeDirectory
     * @return PosixAccount
     */
    public function setHomeDirectory($homeDirectory)
    {
        $this->homeDirectory->set($homeDirectory);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserPasswords()
    {
        return $this->userPasswords;
    }

    /**
     * @param mixed $userPasswords
     * @return PosixAccount
     */
    public function addUserPasswords($userPasswords)
    {
        $this->userPasswords->add($userPasswords);
        return $this;
    }

    /**
     * @param mixed $userPasswords
     * @return PosixAccount
     */
    public function removeUserPasswords($userPasswords)
    {
        $this->userPasswords->remove($userPasswords);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginShell()
    {
        return $this->loginShell->get();
    }

    /**
     * @param mixed $loginShell
     * @return PosixAccount
     */
    public function setLoginShell($loginShell)
    {
        $this->loginShell->set($loginShell);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGecos()
    {
        return $this->gecos->get();
    }

    /**
     * @param mixed $gecos
     * @return PosixAccount
     */
    public function setGecos($gecos)
    {
        $this->gecos->get($gecos);
        return $this;
    }
}