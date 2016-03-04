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
        return $this->getObject(Schemas\PosixGroup::class)->getMeberUids()->map(function ($member) {
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