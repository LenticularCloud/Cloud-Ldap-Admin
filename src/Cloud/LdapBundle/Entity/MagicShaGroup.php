<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Schemas;


class MagicShaGroup extends AbstractGroup
{

    public function __construct(User $user)
    {
        parent::__construct($user->getUsername());
        $data=[
            'cn'=>$user->getCn(),
            'gidnumber' => $user->getUidNumber(),
            'member' => [ 'cn='.$user->getUsername().','.str_replace('ou=people','ou=groups',$user->getDn()) ],
        ];
        foreach($this->getObjectClasses() as $class) {
            $this->addObject($class,$data);
        }

    }


    public function getObjectClasses()
    {
        $classes=parent::getObjectClasses();
        $classes['posixgroup']= Schemas\PosixGroup::class;
        //$classes['sambagroupmapping']= Schemas\SambaGroupMapping::class;
        return $classes;
    }

    /**
     * @return String[]
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * @param $role string
     * @return $this
     */
    public function addRoles($role)
    {
        return $this;
    }

    /**
     * @param $role string
     * @return $this
     */
    public function removeRoles($role)
    {
        return $this;
    }


    public function getMembers()
    {
        return [];
    }

    public function addMember($username)
    {
        return $this;
    }

    public function removeMember($username)
    {
        return $this;
    }
}