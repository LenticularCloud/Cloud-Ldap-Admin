<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use \InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Schemas;

class Service extends AbstractService
{

    /**
     * passwords for this service
     *
     * @Assert\Valid(deep=true)
     *
     * @var array<Password> $passwords
     */
    protected $passwords = array();

    /**
     * @var boolean $masterPasswordEnabled
     */
    protected $masterPasswordEnabled = true;

    /**
     *
     * @var LdapPasswordEncoderInterface $encoder
     */
    protected $encoder;

    /**
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->encoder = new CryptEncoder();
    }

    public function getObjectClasses()
    {
        $classes = parent::getObjectClasses();
        $classes['shadowaccount'] = Schemas\ShadowAccount::class;

        return $classes;
    }

    public function afterAddObject($class)
    {
        if ($class === Schemas\ShadowAccount::class) {
            $this->passwords = [];
            foreach ($this->getObject(Schemas\ShadowAccount::class)->getUserPasswords() as $password) {
                $password = $this->encoder->parsePassword($password);
                $this->passwords[$password->getId()] = $password;
            }
        }
    }

    /**
     *
     * @return array<Password>
     */
    public function getPasswords()
    {
        return $this->passwords;
    }

    /**
     *
     * @return Password
     */
    public function getPassword($passwordId=null)
    {
        if($passwordId===null) {
            throw new InvalidArgumentException("passwordId not found");
        }
        if (!isset($this->passwords[$passwordId])) {
            throw new InvalidArgumentException("passwordId not found");
        }

        return $this->passwords[$passwordId];
    }

    /**
     *
     * @return Password
     */
    public function hasPassword($passwordId=null)
    {
        if($passwordId===null) {
            return false;
        }
        return isset($this->passwords[$passwordId]);
    }

    /**
     *
     * @param Password $password
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function addPassword(Password $password)
    {
        if (isset($this->passwords[$password->getId()]))
            throw new InvalidArgumentException("passwordId is in use");
        $this->passwords[$password->getId()] = $password;
        if ($password->getService() !== $this) {
            $password->setService($this);
        }
        return $this;
    }

    /**
     *
     * @param Password $password
     * @return Service
     */
    public function removePassword(Password $password)
    {
        if (!isset($this->passwords[$password->getId()])) {
            return $this;
        }
        unset($this->passwords[$password->getId()]);
        return $this;
    }

    public function isMasterPasswordEnabled()
    {
        if ($this->getObject(Schemas\CloudService::class) !== null) {
            return $this->getObject(Schemas\CloudService::class)->isMasterPasswordEnabled();
        }
        return false;
    }

    public function setMasterPasswordEnabled($masterPasswordEnabled)
    {
        $this->getObject(Schemas\CloudService::class)->setMasterPasswordEnabled($masterPasswordEnabled);
        return $this;
    }

    /**
     * @return LdapPasswordEncoderInterface
     */
    public function getEncoder()
    {
        return $this->encoder;
    }


    protected function serviceEnabled()
    {
        $this->passwords = $this->getUser()->getPasswords();
        foreach ($this->passwords as $password) {
            $this->attributes['userpassword']->add($password->getAttribute());
        }
    }
}
