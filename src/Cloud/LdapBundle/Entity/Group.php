<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Schemas;
use InvalidArgumentException;

class Group extends AbstractEntity
{

    /**
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2,minMessage="Name must be at least {{ limit }} characters long")
     * @Assert\Regex("/^[a-zA-Z0-9_.-]+$/")
     *
     * @var String $username
     */
    private $name;

    /**
     *
     */
    private $roles = array();

    /**
     * @TODO think about that
     *
     * @var boolean
     */
    private $enable;

    public function __construct($name, array $roles = array(), $enabled = true)
    {
        $this->name = $name;
        $this->roles = $roles;
        $this->enable = $enabled;
    }

    public function getObjectClasses()
    {
        return [
            'groupofnames' => Schemas\GroupOfNames::class,
            'lenticulargroup' => Schemas\LenticularGroup::class,
        ];
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->getObject(Schemas\GroupOfNames::class)->getCn();
    }

    /**
     * @param String $name
     * @return Group
     */
    public function setName($name)
    {
        $this->getObject(Schemas\GroupOfNames::class)->setCn($name);
        return $this;
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


    /**
     *
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     *
     * @param boolean $enable
     * @return Group
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;
        return $this;
    }
}
