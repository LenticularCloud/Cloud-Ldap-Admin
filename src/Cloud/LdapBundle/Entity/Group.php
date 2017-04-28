<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Schemas;

class Group extends AbstractGroup
{
    /**
     * @var string[]    roles of the class
     */
    private $roles;

    public function getObjectClasses()
    {
        $classes = parent::getObjectClasses();
        $classes['lenticulargroup'] = Schemas\LenticularGroup::class;
        return $classes;
    }

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
}