<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Cloud\LdapBundle\Security\CryptEncoder;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Schemas;
use InvalidArgumentException;

class User extends AbstractUser implements AdvancedUserInterface
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
     * @var array<Service>
     */
    private $services = array();

    /**
     *
     * @var boolean
     */
    private $enable = false;

    /**
     * @var string
     */
    private $passwordEncoder = CryptEncoder::class;

    public function __construct($username, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        parent::__construct();
        $this->username = $username;
        $this->setEnable($enabled);
    }

    public function getObjectClasses()
    {
        return [
            'inetorgperson' => Schemas\InetOrgPerson::class,
            'shadowaccount' => Schemas\ShadowAccount::class,
            'lenticularuser' => Schemas\LenticularUser::class,
            //'posixaccount' => Schemas\PosixAccount::class,
        ];
    }

    public function afterAddObject($class)
    {
        switch ($class) {
            case Schemas\ShadowAccount::class:

                $encoder = new CryptEncoder();
                $this->passwords = [];
                foreach ($this->getAttributes()->get('userpassword') as $password) {
                    $password = $encoder->parsePassword($password);
                    $this->passwords[$password->getId()] = $password;
                }
                break;
            case Schemas\LenticularUser::class:
                $this->roles = $this->getObject(Schemas\LenticularUser::class)->getAuthRoles();
                $this->enable = $this->getRoles() > 0;

                if ($this->getObject(Schemas\LenticularUser::class)->getUid() === null) {
                    $this->getObject(Schemas\LenticularUser::class)->setUid($this->username);
                } else {
                    $this->username = $this->getObject(Schemas\LenticularUser::class)->getUid();
                }
                break;
            case Schemas\InetOrgPerson::class:
                $object = $this->getObject(Schemas\InetOrgPerson::class);
                if ($object->getSn() == null) {
                    $object->setSn($this->username);
                }
                if ($object->getCn() == null) {
                    $object->setCn($this->username);
                }
        }
    }

    public function __sleep()
    {
        return array('username', 'enable');
    }

    public function getRoles()
    {
        return $this->getObject(Schemas\LenticularUser::class)->getAuthRoles();
    }

    public function addRole($role)
    {
        $this->getObject(Schemas\LenticularUser::class)->addAuthRole($role);
        return $this;
    }

    public function setRoles(array $roles)
    {
        $this->getAttributes('userpassword')->clear();
        foreach ($roles as $role) {
            $this->getObject(Schemas\LenticularUser::class)->addAuthRole($role);
        }
        return $this;
    }

    public function removeRole($role)
    {
        $this->getObject(Schemas\LenticularUser::class)->removeAuthRole($role);
        return $this;
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
        foreach ($this->services as $service) {
            if ($service->isMasterPasswordEnabled()) {
                $service->addPassword(clone $password);
            }
        }

        if ($password->getPasswordPlain() === null) {
            if ($password->getEncoder() !== $this->passwordEncoder) {
                throw new \InvalidArgumentException();
            }
        } else {
            $att = new Attribute();
            $password->setAttribute($att);
            call_user_func($this->passwordEncoder . '::encodePassword', $password);
        }

        if (isset($this->passwords[$password->getId()])) {
            $this->removePassword($this->passwords[$password->getId()]);
        }
        $this->passwords[$password->getId()] = $password;
        $this->getAttributes()->get('userpassword')->add($password->getAttribute());
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
        $this->getAttributes()->get('userpassword')->removeElement($this->passwords[$password->getId()]->getAttribute());

        foreach ($this->services as $service) {
            if ($service->isMasterPasswordEnabled()) {
                $service->removePassword($password);
            }
        }

        if ($this->passwords[$password->getId()]->getUser() === $this) {
            $this->passwords[$password->getId()]->setUser(null);
        }
        unset($this->passwords[$password->getId()]);
        return $this;
    }

    /**
     *
     * @param Service $service
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function addService(AbstractService $service)
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
    public function removeService(AbstractService $service)
    {
        if (!isset($this->services[$service->getName()])) {
            throw InvalidArgumentException("service not in the list");
        }
        unset($this->services[$service->getName()]);
        if ($service->getUser() === $this) {
            $service->setUser(null);
        }
        return $this;
    }

    /**
     *
     * @return AbstractService[]
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

    public function getAltEmail()
    {
        return $this->getObject(Schemas\LenticularUser::class)->getAltMail();
    }

    public function setAltEmail($altEmail)
    {
        return $this->getObject(Schemas\LenticularUser::class)->setAltMail($altEmail);
    }

    public function getGivenName()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getGivenName();
    }

    public function setGivenName($givenName)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setGivenName($givenName);
    }

    public function getSureName()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getSn();
    }

    public function setSureName($sureName)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setSn($sureName);
    }

    public function getDisplayName()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getSn();
    }

    public function setDisplayName($displayName)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setSn($displayName);
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *false
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->enable;
    }
}
