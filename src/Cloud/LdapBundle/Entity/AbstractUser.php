<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;

abstract class AbstractUser extends AbstractEntity
{

    public function getGroups() {
        return [];
    }

    public function addGroup(AbstractGroup $group) {
        return $this;
    }

    public function removeGroup(AbstractGroup $group) {
        return $this;
    }
}