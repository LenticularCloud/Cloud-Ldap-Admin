<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Schemas;

class PosixGroup extends  AbstractGroup
{


    public function getGid(){
        return $this->getObject(Schemas\PosixGroup::class)->getGid();
    }

    public function setGid($gid){
        return $this->getObject(Schemas\PosixGroup::class)->setGid($gid);
    }

    public function getMembers()
    {
        return $this->getObject(Schemas\PosixGroup::class)->getMemberUids();
    }

    public function addMember($username)
    {
        $this->getObject(Schemas\PosixGroup::class)->addMemberUid($username);
        return $this;
    }

    public function removeMember($username)
    {
        $this->getObject(Schemas\PosixGroup::class)->removeMemberUid($username);
        return $this;
    }
/*
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
    }*/

}