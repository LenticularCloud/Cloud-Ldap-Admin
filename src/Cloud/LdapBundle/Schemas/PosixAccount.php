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
     * attributetype ( 1.3.6.1.1.1.1.0 NAME 'uidNumber'
     * DESC 'An integer uniquely identifying a user in an administrative domain'
     * EQUALITY integerMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.27 SINGLE-VALUE )
     *
     * @var Attribute
     * @LDAP\Attribute(type="number")\
     */
    private $uidNumber;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="number")
     */
    private $gidNumber;

    /**
     * attributetype ( 1.3.6.1.1.1.1.3 NAME 'homeDirectory'
     * DESC 'The absolute path to the home directory'
     * EQUALITY caseExactIA5Match
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.26 SINGLE-VALUE )
     *
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
     * attributetype ( 1.3.6.1.1.1.1.4 NAME 'loginShell'
     * DESC 'The path to the login shell'
     * EQUALITY caseExactIA5Match
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.26 SINGLE-VALUE )
     *
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
     *
     * @Assert\NotBlank()
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
     *
     * @Assert\NotBlank()
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
     *
     * @Assert\NotBlank()
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
     *
     * @Assert\NotBlank()
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
     *
     * @Assert\NotBlank()
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