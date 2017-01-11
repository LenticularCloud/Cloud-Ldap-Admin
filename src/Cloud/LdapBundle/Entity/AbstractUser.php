<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;

abstract class AbstractUser extends AbstractEntity
{
    private $groups = [];

    /**
     * @return AbstractGroup[]
     */
    public function getGroups()
    {
        return [];
    }

    /**
     * @param AbstractGroup $group
     * @return $this
     */
    public function addGroup(AbstractGroup $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * @param AbstractGroup $group
     * @return $this
     */
    public function removeGroup(AbstractGroup $group)
    {
        $key = array_search($group,$this->groups);
        if($key!==false){
            unset($this->groups[$key]);
        }
        return $this;
    }

    /**
     * @return string  Encoder class, has to implement LdapPasswordEncoderInterface
     */
    public abstract function getEncoder();
}