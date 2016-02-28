<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'Additional attributes for shadow passwords'
 * SUP top AUXILIARY
 * MUST uid
 * MAY ( userPassword $ shadowLastChange $ shadowMin $
 * shadowMax $ shadowWarning $ shadowInactive $
 * shadowExpire $ shadowFlag $ description ) )
 * @LDAP\Schema()
 */
class ShadowAccount
{
    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     * @Assert\NotNull()
     */
    private $uid;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(name="userPassword",type="array")
     */
    private $userPasswords;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $shadowLastChange;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $shadowExpire;

    public function __construct()
    {
        $this->uid=new Attribute();
        $this->userPasswords=new ArrayCollection();
        $this->shadowLastChange=new Attribute();
        $this->shadowExpire=new Attribute();
    }

    /**
     * @return Attribute
     */
    public function getUid()
    {
        return $this->uid->get();
    }

    /**
     * @param Attribute $uid
     * @return ShadowAccount
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getUserPasswords()
    {
        return $this->userPasswords;
    }

    /**
     * @param Attribute $userPassword
     * @return ShadowAccount
     */
    public function addUserPassword(Attribute $userPassword)
    {
        $this->userPasswords->add($userPassword);
        return $this;
    }

    /**
     * @param Attribute $userPassword
     * @return ShadowAccount
     */
    public function removeUserPassword(Attribute $userPassword)
    {
        $this->userPasswords->remove($userPassword);
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getShadowLastChange()
    {
        return $this->shadowLastChange;
    }

    /**
     * @param Attribute $shadowLastChange
     * @return ShadowAccount
     */
    public function setShadowLastChange($shadowLastChange)
    {
        $this->shadowLastChange = $shadowLastChange;
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getShadowExpire()
    {
        return $this->shadowExpire;
    }

    /**
     * @param Attribute $shadowExpire
     * @return ShadowAccount
     */
    public function setShadowExpire($shadowExpire)
    {
        $this->shadowExpire = $shadowExpire;
        return $this;
    }


}