<?php
namespace Cloud\LdapBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Schemas;

abstract class AbstractService extends AbstractUser
{
    /**
     * @var User $user
     */
    private $user = null;

    /**
     * @var string
     */
    private $name;

    /**
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
    }

    public function afterAddObject($class)
    {
        if ($class === Schemas\ShadowAccount::class) {
            $encoder = new CryptEncoder();
            $this->passwords = [];
            foreach ($this->getObject(Schemas\ShadowAccount::class)->getUserPasswords() as $password) {
                $password = $encoder->parsePassword($password);
                $this->passwords[$password->getId()] = $password;
            }
        }
    }

    public function getObjectClasses()
    {
        return [
            'service' => Schemas\CloudService::class,
        ];
    }


    /**
     * name of the service
     *
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z0-9_-]+$/")
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * passwords for this service
     *
     * @Assert\Valid(deep=true)
     *
     * @return array<Password>
     */
    abstract public function getPasswords();

    /**
     * @param   string $passwordId
     * @return Password
     */
    abstract public function getPassword($passwordId = null);

    /**
     *
     * @return Password
     */
    abstract public function hasPassword($passwordId = null);

    /**
     *
     * @param Password $password
     * @return \Cloud\LdapBundle\Entity\Service
     */
    abstract public function addPassword(Password $password);

    /**
     *
     * @param Password $password
     * @return Service
     */
    abstract public function removePassword(Password $password);

    public function isMasterPasswordEnabled()
    {
        if ($this->getObject(Schemas\CloudService::class) !== null) {
            return $this->getObject(Schemas\CloudService::class)->isMasterPasswordEnabled();
        }

        return false;
    }

    public function setMasterPasswordEnabled($masterPasswordEnabled)
    {
        if ($masterPasswordEnabled == false && $this->isMasterPasswordEnabled()) {
            foreach ($this->getPasswords() as $password) {
                if ($password->isMasterPassword()) {
                    $this->removePassword($password);
                }
            }
        } elseif ($masterPasswordEnabled && $this->isMasterPasswordEnabled() == false) {
            foreach ($this->getUser()->getPasswords() as $password) {
                if ($password->getEncoder() == $this->getEncoder()) {
                    $this->addPassword(clone $password);
                }
            }
        }
        $this->getObject(Schemas\CloudService::class)->setMasterPasswordEnabled($masterPasswordEnabled);

        return $this;
    }

    public function isEnabled()
    {
        return $this->objects->count() > 0;
    }

    public function setEnabled($value)
    {
        if ($this->isEnabled() === true && $value !== false || $this->isEnabled() !== true && $value !== true) { //nothing changed
            return $this;
        } elseif (!$value) { // disable
            $this->serviceDisabled();
            $this->objects = new ArrayCollection();
            $this->attributes = new ArrayCollection();

            return $this;
        }
        // enable
        foreach ($this->getObjectClasses() as $class) {
            $this->addObject($class);
        }
        $this->attributes['uid']->set($this->user->getUsername());

        $this->serviceEnabled();

        return $this;
    }

    protected function serviceEnabled()
    {
        // add masterpaswords
        if ($this->isMasterPasswordEnabled()) {
            foreach ($this->getUser()->getPasswords() as $password) {
                if ($password->getEncoder() == $this->getEncoder()) {
                    $this->addPassword(clone $password);
                }
            }
        }
    }

    protected function serviceDisabled()
    {

    }

    abstract public function maxPasswords();

    /**
     *
     * @return \Cloud\LdapBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        if (!in_array($this, $user->getServices())) {
            $this->user->addService($this);
        }

        return $this;
    }

    /**
     * @return LdapPasswordEncoderInterface
     */
    abstract public function getEncoder();
}
