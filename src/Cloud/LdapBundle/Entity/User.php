<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use \Cloud\LdapBundle\Entity\Password;
use \Cloud\LdapBundle\Entity\Service;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Security\Core\User\User as BaseUser;

class User extends BaseUser
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
     * @Assert\NotBlank()
     *
     * @var Array<Password> $passwords
     */
    private $passwords = array();

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

    public function __construct($username, $password, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true) {
        parent::__construct($username, $password, $roles, $enabled , $userNonExpired, $credentialsNonExpired , $userNonLocked );
    }

    /**
     * check for same id in user object and services
     * 
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        foreach ($this->getServices() as $service) {
            foreach ($service->getPasswords() as $password) {
                if(isset($this->passwords[$password->getId()])) {
                    $context->buildViolation('password ids can\'t be in user object and in a service')
                        ->atPath('passwords')
                        ->addViolation();
                    return;
                }
            }
        }
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
        if (!isset($this->passwords[$passwordId]))
            return null;
        
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
