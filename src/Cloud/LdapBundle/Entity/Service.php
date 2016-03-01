<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
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
     *
     * @var string $encoder
     */
    protected $encoder = CryptEncoder::class;

    /**
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
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
            foreach ($this->getAttributes()->get('userpassword') as $attribute) {

                $password = call_user_func($this->encoder . '::parsePassword', $attribute);
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
    public function getPassword($passwordId = null)
    {
        if ($passwordId === null) {
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
    public function hasPassword($passwordId = null)
    {
        if ($passwordId === null) {
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
        if ($password->getPasswordPlain() === null) {
            if ($password->getEncoder() !== $this->getEncoder()) {
                throw new \InvalidArgumentException();
            }
        } else {
            $att = new Attribute();
            $password->setAttribute($att);
            call_user_func($this->getEncoder() . '::encodePassword', $password);
        }

        if (isset($this->passwords[$password->getId()])) {
            $this->removePassword($this->passwords[$password->getId()]);
        }
        $this->passwords[$password->getId()] = $password;
        $this->getAttributes()->get('userpassword')->add($password->getAttribute());
        if ($password->getService() !== $this) {
            $password->setService($this);
        }
        if ($password->isMasterPassword()) {
            $password->setMasterPassword(false);
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
        $this->getAttributes()->get('userpassword')->removeElement($this->passwords[$password->getId()]->getAttribute());
        unset($this->passwords[$password->getId()]);
        return $this;
    }

    /**
     * @return string
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

    public function maxPasswords()
    {
        return PHP_INT_MAX;
    }
}
