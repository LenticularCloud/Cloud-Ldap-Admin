<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'RFC2256: a person'
 * SUP top STRUCTURAL
 * MUST ( sn $ cn )
 * MAY ( userPassword $ telephoneNumber $ seeAlso $ description ) )
 * @LDAP\Schema()
 */
class Person
{
    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $sn;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $cn;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(name="userPassword",type="array")
     */
    private $userPasswords;


    public function __construct()
    {
        $this->sn=new Attribute();
        $this->cn=new Attribute();
        $this->userPasswords=new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getSn()
    {
        return $this->sn->get();
    }

    /**
     * @param mixed $sn
     * @return Person
     */
    public function setSn($sn)
    {
        $this->sn->set($sn);
        return $this;
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
     * @return Person
     */
    public function setCn($cn)
    {
        $this->cn->set($cn);
        return $this;
    }

    /**
     * @param string $userPasswords
     */
    public function addUserPassword($userPasswords)
    {
        $this->userPasswords->add($userPasswords);
    }

    /**
     * @param string $userPasswords
     */
    public function removeUserPassword($userPasswords)
    {
        $this->userPasswords->remove($userPasswords);
    }
    /**
     * @param string $userPasswords
     */
    public function getUserPasswords($userPasswords)
    {
        $this->userPasswords->remove($userPasswords);
    }
}