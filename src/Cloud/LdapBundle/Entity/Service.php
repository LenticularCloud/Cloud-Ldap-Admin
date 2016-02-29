<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use \InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Schemas;

class Service extends AbstractEntity
{

    /**
     * name of the service
     *
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z0-9_-]+$/")
     *
     * @var String $name
     */
    protected $name;

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
     * @var User $user
     */
    protected $user = null;

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
        parent::__construct();
        $this->encoder = new CryptEncoder();
        $this->name = $name;
    }

    public function getObjectClasses()
    {
        return [
            'shadowaccount' => Schemas\ShadowAccount::class,
            'service' => Schemas\CloudService::class
        ];
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

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getPassword($passwordId)
    {
        if (!isset($this->passwords[$passwordId])) {
            throw new InvalidArgumentException("passwordId not found");
        }

        return $this->passwords[$passwordId];
    }

    /**
     *
     * @return Password
     */
    public function hasPassword($passwordId)
    {
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
        if($this->getObject(Schemas\CloudService::class) !== null) {
            return $this->getObject(Schemas\CloudService::class)->isMasterPasswordEnabled();
        }
        return false;
    }

    public function setMasterPasswordEnabled($masterPasswordEnabled)
    {
        $this->getObject(Schemas\CloudService::class)->setMasterPasswordEnabled($masterPasswordEnabled);
        return $this;
    }

    public function isEnabled()
    {
        return $this->objects->count()>0;
    }

    public function setEnabled($value)
    {
        if($this->isEnabled()===true && $value!==false || $this->isEnabled()!==true && $value!==true)
        { //nothing changed
            return $this;
        }elseif(!$value)
        { // disable
            $this->objects=new ArrayCollection();
            $this->attributes=new ArrayCollection();
            return $this;
        }
        // enable

        foreach($this->getObjectClasses() as $class) {
            $this->addObject($class);
        }
        $this->attributes['uid']=$this->user->getAttributes()->get('uid');

        $this->passwords=$this->user->getPasswords();
        foreach($this->passwords as $password) {
            //$this->attributes['userpassword']->add($password->getAttribute());
        }

        return $this;
    }

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
    public function getEncoder()
    {
        return $this->encoder;
    }
}
