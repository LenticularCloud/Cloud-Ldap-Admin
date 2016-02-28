<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;
use Cloud\LdapBundle\Security\CryptEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use \Cloud\LdapBundle\Entity\Password;
use \Cloud\LdapBundle\Entity\Service;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Security\Core\User\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Cloud\LdapBundle\Schemas;
use InvalidArgumentException;

class User extends AbstractEntity implements UserInterface
{

    private $username;

    /**
     * main passwords for this user
     *
     * @Assert\Valid(deep=true)
     * @Assert\NotBlank(message="You have to set at min. one master password.")
     *
     * @var Array<Password> $passwords
     */
    private $passwords = array();

    /**
     *
     */
    private $roles = array();

    /**
     * assoziativ array with service info
     *
     * @Assert\Valid(deep=true)
     *
     * @var AssoziativArray<Service> @Assert\Valid(deep=true)
     */
    private $services = array();

    /**
     * @TODO think about that
     *
     * @var boolean
     */
    private $enable;

    public function __construct($username, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        parent::__construct();
        $this->username = $username;
        foreach ($roles as $role) {
            $this->addRoles($role);
        }
        $this->setEnable($enabled);
    }

    public function getObjectClasses()
    {
        return [
            'inetorgperson' => Schemas\InetOrgPerson::class,
            'posixaccount' => Schemas\PosixAccount::class,
            'shadowaccount' => Schemas\ShadowAccount::class,
        ];
    }

    public function afterAddObject($class)
    {

        if ($class === Schemas\ShadowAccount::class) {

            $this->username = $this->getObject(Schemas\ShadowAccount::class)->getUid();

            $encoder = new CryptEncoder();
            $this->passwords = [];
            foreach ($this->getObject(Schemas\ShadowAccount::class)->getUserPasswords() as $password) {
                $password = $encoder->parsePassword($password);
                $this->passwords[$password->getId()] = $password;
            }
        }
    }

    public function getRoles()
    {
        //return $this->roles;
        return ['ROLE_USER'];
    }

    public function addRoles($role)
    {
        $this->roles[] = $role;
    }

    public function getSalt()
    {
        return "";
    }

    public function eraseCredentials()
    {

    }


    /**
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2,minMessage="Username must be at least {{ limit }} characters long")
     * @Assert\Regex("/^[a-zA-Z0-9_.-]+$/")
     *
     * @return String
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @return Array<Password>
     */
    public function getPasswords()
    {
        return $this->passwords;
    }

    /**
     *
     * @return Password
     */
    public function getPassword($passwordId = null)
    {
        if ($passwordId == null) {
            return null;
        }
        if (!isset($this->passwords[$passwordId])) {
            throw new InvalidArgumentException("passwordId not found");
        }

        return $this->passwords[$passwordId];
    }

    /**
     *
     * @param Password $password
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function addPassword(Password $password)
    {
        if (isset($this->passwords[$password->getId()])) {
            $this->removePassword($this->passwords[$password->getId()]);
        }
        $this->passwords[$password->getId()] = $password;
        if ($password->getUser() !== $this) {
            $password->setUser($this);
        }
        if (!$password->isMasterPassword()) {
            $password->setMasterPassword(true);
        }
        return $this;
    }

    /**
     *
     * @param Password $password
     */
    public function removePassword(Password $password)
    {
        $this->attributes->removeElement($this->passwords[$password->getId()]->getAttribute());

        foreach($this->services as $service) {
            if($service->isMasterPasswordEnabled()) {
                $service->removePassword($password);
            }
        }

        unset($this->passwords[$password->getId()]);
        if ($password->getUser() === $this) {
            $password->setUser(null);
        }
        return $this;
    }

    /**
     *
     * @param Service $service
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function addService(Service $service)
    {
        if (strlen($service->getName()) <= 0) {
            throw new \InvalidArgumentException("service name can't be null");
        }
        $this->services[$service->getName()] = $service;
        if ($service->getUser() !== $this) {
            $service->setUser($this);
        }

        return $this;
    }

    /**
     *
     * @param Service $service
     */
    public function removeService(Service $service)
    {
        if (!isset($this->services[$service->getName()])) {
            throw \InvalidArgumentException("service not in the list");
        }
        unset($this->services[$service->getName()]);
        if ($service->getUser() === $this) {
            $service->setUser(null);
        }
        return $this;
    }

    /**
     *
     * @return AssoziativArray<Service>
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     *
     * @return Service
     */
    public function getService($name)
    {
        return isset($this->services[$name]) ? $this->services[$name] : null;
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
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;
        return $this;
    }

    public function getEmail()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getMail();
    }

    public function setEmail($email)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setMail($email);
    }
}
