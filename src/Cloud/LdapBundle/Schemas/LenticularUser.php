<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DESC ''
 * SUP top AUXILIARY
 * MUST uid
 * MAY (
 *  authRole
 * )
 * @LDAP\Schema(name="LenticularUser")
 */
class LenticularUser
{
    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(name="authRole",type="array")
     */
    private $authRoles;

    /**
     * Alternative Email address
     *
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $altMail;

    public function __construct()
    {
        $this->uid = new Attribute();
        $this->authRoles = new ArrayCollection();
        $this->altMail = new Attribute();
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
     * @return LenticularUser
     */
    public function setUid($uid)
    {
        $this->uid->set($uid);
        return $this;
    }

    /**
     * @return array
     */
    public function getAuthRoles()
    {
        return $this->authRoles->map(function ($role) {
            return $role->get();
        })->toArray();
    }

    /**
     * @param Attribute $authRole
     * @return LenticularUser
     */
    public function addAuthRole($authRole)
    {
        $this->removeAuthRole($authRole);
        $this->authRoles->add(new Attribute($authRole));
        return $this;
    }

    /**
     * @param string $role
     * @return LenticularUser
     */
    public function removeAuthRole($role)
    {
        foreach($this->authRoles as $authRole) {
            if($authRole->get()==$role) {
                $this->authRoles->removeElement($authRole);
                return $this;
            }
        }
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getAltMail()
    {
        return $this->altMail->get();
    }

    /**
     * @param Attribute $altMail
     * @return LenticularUser
     */
    public function setAltMail($altMail)
    {
        $this->altMail->set($altMail);
        return $this;
    }
}