<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;
use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Schemas;
use InvalidArgumentException;

class AbstractGroup extends AbstractEntity
{

    /**
     * @TODO think about that
     *
     * @var boolean
     */
    private $enable = true;

    private $name;

    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
    }

    public function getObjectClasses()
    {
        return [
            'groupofnames' => Schemas\GroupOfNames::class,
        ];
    }

    /**
     * example: ou=users,dc=example,dc=org
     *
     * @return string
     */
    protected function getPostDn()
    {
        preg_match('#^cn=[^,]+,[^,]+,(?<postDN>.*)$#', $this->getDn(), $match);

        return $match['postDN'];
    }

    /**
     * @return String
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2,minMessage="Name must be at least {{ limit }} characters long")
     * @Assert\Regex("/^[a-zA-Z0-9_.-]+$/")
     */
    public function getName()
    {
        if ($this->getObject(Schemas\GroupOfNames::class) !== null) {
            return $this->getObject(Schemas\GroupOfNames::class)->getCn();
        } else {
            return $this->name;
        }
    }

    /**
     * @param String $name
     * @return Group
     */
    public function setName($name)
    {
        if ($this->getObject(Schemas\GroupOfNames::class) !== null) {
            $this->getObject(Schemas\GroupOfNames::class)->setCn($name);
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @return String[] array with members dns
     */
    public function getMembers()
    {
        return $this->getObject(Schemas\GroupOfNames::class)->getMembers();
    }

    /**
     * @param string $memberDN name of a member
     * @return AbstractGroup
     */
    public function addMember($memberDN)
    {
        $this->getObject(Schemas\GroupOfNames::class)->addMember($memberDN);

        return $this;
    }

    /**
     * @param string $memberDN name of a member
     * @return AbstractGroup
     */
    public function removeMember($memberDN)
    {
        $this->getObject(Schemas\GroupOfNames::class)->removeMembers($memberDN);

        return $this;
    }

    /**
     * @return String[] array with members
     */
    public function hasMember($member)
    {
        foreach ($this->getMembers() as $_member) {
            if ($member === $_member) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getObjects()->count() > 0;
    }

    /**
     *
     * @param boolean   $value final state
     * @return Group
     */
    public function setEnabled($value)
    {
        if ($this->isEnabled() === true && $value !== false || $this->isEnabled() !== true && $value !== true) { //nothing changed
            return $this;
        } elseif (!$value) { // disable
            $this->groupDisabled();
            $this->objects = new ArrayCollection();
            $this->attributes = new ArrayCollection();

            return $this;
        }
        // enable
        foreach ($this->getObjectClasses() as $class) {
            $this->addObject($class);
        }
        $this->attributes['cn']->set($this->name);

        $this->groupEnabled();

        return $this;
    }

    protected function groupDisabled()
    {

    }

    protected function groupEnabled()
    {

    }
}
