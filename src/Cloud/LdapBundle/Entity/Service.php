<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;

class Service
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
     * @var Array<Password> $passwords
     */
    protected $passwords = array();
    
    /**
     * @var boolean $masterPasswordEnabled
     */
    protected $masterPasswordEnabled=false;
    
    /**
     * @var boolean $enabled
     */
    protected $enabled=false;
    
    /**
     * @var User $user
     */
    protected $user=null;
    
    /**
     * 
     * @var LdapPasswordEncoderInterface    $encoder
     */
    protected $encoder;

    /**
     *
     * @param string $name            
     */
    public function __construct($name)
    {
        $this->encoder=new CryptEncoder();
        $this->name = $name;
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
    public function getPassword($passwordId)
    {
        if (! isset($this->passwords[$passwordId])) {
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
        if($password->getService()!==$this) {
            $password->setService($this);
        }
        return $this;
    }

    /**
     *
     * @param Password $password            
     */
    public function removePassword(Password $password)
    {
        if (! isset($this->passwords[$password->getId()])) {
            throw InvalidArgumentException("passwordId not found");
        }
        unset($this->passwords[$password->getId()]);
        return $this;
    }

    public function isMasterPasswordEnabled()
    {
        return $this->masterPasswordEnabled;
    }

    public function setMasterPasswordEnabled($masterPasswordEnabled)
    {
        $this->masterPasswordEnabled = $masterPasswordEnabled;
        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
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
        if(!in_array($this,$user->getServices())) {
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
