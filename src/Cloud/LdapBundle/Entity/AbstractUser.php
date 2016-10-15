<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;

abstract class AbstractUser extends AbstractEntity
{

    /**
     * @return AbstractGroup[]
     */
    public function getGroups() {
        return [];
    }

    /**
     * @param AbstractGroup $group
     * @return $this
     */
    public function addGroup(AbstractGroup $group) {
        return $this;
    }

    /**
     * @param AbstractGroup $group
     * @return $this
     */
    public function removeGroup(AbstractGroup $group) {
        return $this;
    }

    /**
     * @return string  Encoder class, has to implement LdapPasswordEncoderInterface
     */
    public abstract function getEncoder();
}