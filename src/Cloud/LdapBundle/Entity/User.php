<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use \Cloud\LdapBundle\Entity\Password;
use \Cloud\LdapBundle\Entity\Service;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Security\Core\User\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{

    /**
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2,minMessage="Username must be at least {{ limit }} characters long")
     * @Assert\Regex("/^[a-zA-Z0-9_-]+$/")
     * @TODO think about '.' in the allowd chars
     *
     * @var String $username
     */
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
    private $roles=array();

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

    public function __construct($username, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true) {
        $this->username=$username;
        $this->roles=$roles;
        $this->enable=$enabled;
        //parent::__construct($username, "", $roles, $enabled , $userNonExpired, $credentialsNonExpired , $userNonLocked );
    }
    
    public function getRoles()
    {
        return $this->roles;
    }
    
    public function addRoles($role)
    {
        $this->roles[]=$role;
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
     * @return String
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @param unknown $username            
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
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
    public function getPassword($passwordId=null)
    {
        if (! isset($this->passwords[$passwordId])) {
            throw new InvalidArgumentException("passwordId not found");
        }
        
        return $this->passwords[$passwordId];
    }

    /**
     *
     * @param Password $password            
     * @return \Cloud\LdapBundle\Entity\Service
     * @throws \InvalidArgumentException
     */
    public function addPassword(Password $password)
    {
        if (isset($this->passwords[$password->getId()]))
            throw new \InvalidArgumentException("passwordId is in use");
        $this->passwords[$password->getId()] = $password;
        if($password->getUser()!==$this) {
            $password->setUser($this);
        }
        if(!$password->isMasterPassword()) {
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
        if (! isset($this->passwords[$password->getId()])) {
            throw \InvalidArgumentException("password not in the list");
        }
        unset($this->passwords[$password->getId()]);
        if($password->getUser()===$this) {
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
        if(strlen($service->getName())<=0) {
            throw new \InvalidArgumentException("service name can't be null");
        }
        $this->services[$service->getName()] = $service;
        if($service->getUser()!==$this) {
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
        if (! isset($this->services[$service->getId()])) {
            throw \InvalidArgumentException("service not in the list");
        }
        unset($this->services[$service->getId()]);
        if($service->getUser()===$this) {
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
}
