<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DESC ''
 * SUP top STRUCTURAL
 * MUST cn
 * MAY ( authRole )
 *
 * @LDAP\Schema()
 */
class LenticularGroup
{
    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $cn;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(name="authRole",type="array")
     */
    private $authRoles;

    public function __construct()
    {
        $this->uid = new Attribute();
        $this->authRoles = new Attribute();
    }

    /**
     * @return string
     */
    public function getCn()
    {
        return $this->cn->get();
    }

    /**
     * @param string $cn
     * @return LenticularUser
     */
    public function setUid($cn)
    {
        $this->cn->set($cn);
        return $this;
    }

    /**
     * @return array
     */
    public function getAuthRoles()
    {
        return $this->authRoles->map(function ($value) {
            return $value->get();
        })->toArray();
    }

    /**
     * @param Attribute $authRole
     * @return LenticularUser
     */
    public function addAuthRole($authRole)
    {
        $this->authRoles->add($authRole);
        return $this;
    }

    /**
     * @param Attribute $authRole
     * @return LenticularUser
     */
    public function removeAuthRole($authRole)
    {
        foreach($this->authRoles as $authRole) {
            if($authRole->get() == $authRole) {
                $this->authRoles->remove($authRole);
            }
        }
        return $this;
    }


}