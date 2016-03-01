<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'a cloud service'
 * SUP top STRUCTURAL
 * MUST ( uid )
 * MAY ( masterpasswordenable ) )
 * @LDAP\Schema(name="Service")
 */
class CloudService
{
    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="bool")
     */
    private $masterPasswordEnable;

    public function __construct()
    {
        $this->uid = new Attribute();
        $this->masterPasswordEnable = new Attribute();
    }

    /**
     * @return Attribute
     */
    public function isMasterPasswordEnabled()
    {
        return $this->masterPasswordEnable->get() === "TRUE";
    }

    /**
     * @param boolean $masterPasswordEnable
     * @return CloudService
     */
    public function setMasterPasswordEnabled($masterPasswordEnable)
    {
        if ($masterPasswordEnable) {
            $this->masterPasswordEnable->set("TRUE");
        } else {
            $this->masterPasswordEnable->set("FALSE");
        }
        return $this;
    }
}