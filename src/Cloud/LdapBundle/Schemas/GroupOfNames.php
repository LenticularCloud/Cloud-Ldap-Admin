<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'RFC2256: a group of names (DNs)'
 * SUP top STRUCTURAL
 * MUST ( member $ cn )
 * MAY ( businessCategory $ seeAlso $ owner $ ou $ o $ description ) )
 * @LDAP\Schema()
 */
class GroupOfNames
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
     * @LDAP/Attribute(name="member",type="array")
     */
    private $members;

    public function __construct()
    {
        $this->cn=new Attribute();
        $this->members=new ArrayCollection();
    }


    /**
     * @return string
     *
     * @Assert\NotBlank()
     */
    public function getCn() {
        return $this->cn->get();
    }

    /**
     * @param $cn
     */
    public function setCn($cn){
        $this->cn->set($cn);
    }


    /**
     * @return array
     * @Assert\Count(min="1",minMessage="member not set")
     */
    public function getMembers()
    {
        return $this->members->map(function($value){return $value->get();})->getValues();
    }

    /**
     * @param string $member
     * @return GroupOfNames
     */
    public function addMember($member)
    {
        $this->removeMember($member);
        $this->members->add(new Attribute($member));
        return $this;
    }

    /**
     * @param string $member
     * @return GroupOfNames
     */
    public function removeMember($member)
    {
        foreach($this->members as $attr) {
            if($attr->get() == $member) {
                $this->members->remove($attr);
                return $this;
            }
        }
        return $this;
    }
}