<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Schemas;

class Group extends AbstractGroup
{

    /**
     * @return String[]
     */
    public function getRoles()
    {
    return $this->getObject(Schemas\LenticularGroup::class)->getAuthRoles();
    }

    /**
     * @param $role string
     * @return $this
     */
    public function addRoles($role)
    {
    $this->getObject(Schemas\LenticularGroup::class)->addAuthRole($role);
    return $this;
    }

    /**
     * @param $role string
     * @return $this
     */
    public function removeRoles($role)
    {
    unset($this->roles[$role]);
    return $this;
    }



    public function getMembers()
    {
        return $this->getObject(Schemas\GroupOfNames::class)->getMembers()->map(function ($member) {
            preg_match('#^uid=(?<uid>[^,]+),.*$#', $member->get(), $match);
            return $match['uid'];
        })->getValues();
    }

    public function addMember($username)
    {
        $uid = 'uid=' . $username . ',ou=Users,'. $this->getPostDn();
        $this->getObject(Schemas\GroupOfNames::class)->getMembers()->add(new Attribute($uid));
        return $this;
    }

    public function removeMember($username)
    {
        $uid = 'uid=' . $username . ',ou=Users,'. $this->getPostDn();
        $members=$this->getObject(Schemas\GroupOfNames::class)->getMembers();
        foreach($members as $member) {
            if($member->get() == $uid) {
                $members->removeElement($member);
            }
        }
        return $this;
    }
}