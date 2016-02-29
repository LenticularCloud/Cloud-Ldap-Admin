<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'Abstraction of a group of accounts'
 * SUP top STRUCTURAL
 * MUST ( cn $ gidNumber )
 * MAY ( userPassword $ memberUid $ description ) )
 * @LDAP\Schema()
 */
class PosixGroup
{
    /**
     * @var string
     *
     * @LDAP\Attribute(type="string")
     */
    private $cn;

    /**
     * @var int
     *
     * @LDAP\Attribute(type="number")
     */
    private $gidNumber;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(type="array")
     */
    private $memberUid;

    /**
     * @return string
     */
    public function getCn()
    {
        return $this->cn;
    }

    /**
     * @param string $cn
     * @return PosixGroup
     */
    public function setCn($cn)
    {
        $this->cn = $cn;
        return $this;
    }

    /**
     * @return int
     */
    public function getGidNumber()
    {
        return $this->gidNumber;
    }

    /**
     * @param int $gidNumber
     * @return PosixGroup
     */
    public function setGidNumber($gidNumber)
    {
        $this->gidNumber = $gidNumber;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMemberUid()
    {
        return $this->memberUid;
    }

    /**
     * @param ArrayCollection $memberUid
     * @return PosixGroup
     */
    public function setMemberUid($memberUid)
    {
        $this->memberUid = $memberUid;
        return $this;
    }
}