<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DESC ''
 * SUP top STRUCTURAL
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

    public function __construct()
    {
        $this->uid = new Attribute();
        $this->authRoles = new ArrayCollection();
    }

    /**
     * @return Attribute
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param Attribute $uid
     * @return LenticularUser
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
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


}