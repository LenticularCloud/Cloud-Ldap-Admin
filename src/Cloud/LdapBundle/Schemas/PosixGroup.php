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
     * attributetype ( 1.3.6.1.1.1.1.12 NAME 'memberUid'
     * EQUALITY caseExactIA5Match
     * SUBSTR caseExactIA5SubstringsMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.26 )
     *
     * @var ArrayCollection
     *
     * @LDAP\Attribute(name="memberUid"type="array")
     */
    private $memberUids;

    /**
     * @return string
     *
     * @Assert\NotBlank()
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
     *
     * @Assert\NotBlank()
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
     *
     * @Assert\NotBlank()
     */
    public function getMemberUids()
    {
        return $this->memberUids;
    }

    /**
     * @param ArrayCollection $memberUid
     * @return PosixGroup
     */
    public function addMemberUid($memberUid)
    {
        $this->removeMemberUid($memberUid);
        $this->memberUids->add(new Attribute($memberUid));
        return $this;
    }

    /**
     * @param ArrayCollection $memberUid
     * @return PosixGroup
     */
    public function removeMemberUid($memberUid)
    {
        foreach($this->memberUids as $attr) {
            if($attr->get() == $memberUid) {
                $this->memberUids->remove($attr);
                return $this;
            }
        }
        return $this;
    }
}