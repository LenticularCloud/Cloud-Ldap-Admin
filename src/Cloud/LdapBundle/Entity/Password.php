<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Password
{

    /**
     * crypt format
     * first part of the salt is the id
     * {CRYPT}$5$rounds=6000$myID=RANDOMSALT$HASH:
     *
     * @var String $hash
     */
    private $hash;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z0-9_-]{2,10}$/")
     *
     * @var String $id
     */
    private $id;

    /**
     * only used if pw changes
     * @Assert\Length(min=6,minMessage = "Your password must be at least {{ limit }} characters long")
     *
     * @var String $password_plain
     */
    private $password_plain;
    
    
    /**
     * 
     * @var User    $user
     */
    private $user;
    
    private $isMasterPassword;
    
    /**
     * 
     * @var Service
     */
    private $service;

    public function __construct($id = null, $password_plain = null,$isMasterPassword=false)
    {
        $this->password_plain = $password_plain;
        $this->id = $id;
        $this->isMasterPassword=$isMasterPassword;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (! isset($this->hash) && ! isset($this->password_plain)) {
            $context->buildViolation('password_plain have to be not null if no hash is set')
                ->atPath('password_plain')
                ->addViolation();
        }
    }

    /**
     *
     * @return the String
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     *
     * @param
     *            $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     *
     * @return the String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * unique
     * allow /(a-Z0-9_-){1,10}/
     *
     * @param String $id            
     */
    public function setId($id)
    {
        if($this->service!==null) {
            $this->service->removePassword($this);
            $this->id = $id;
            $this->service->addPassword($this);
        }else {
            $this->id = $id;
        }
        return $this;
    }

    /**
     *
     * @return the String
     */
    public function getPasswordPlain()
    {
        return $this->password_plain;
    }

    /**
     *
     * @param
     *            $password_plain
     */
    public function setPasswordPlain($password_plain)
    {
        $this->password_plain = $password_plain;
        return $this;
    }

    public function isMasterPassword()
    {
        return $this->isMasterPassword;
    }

    public function setMasterPassword($isMasterPassword)
    {
        $this->isMasterPassword = $isMasterPassword;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * 
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * 
     * @param Service $service
     * @return \Cloud\LdapBundle\Entity\Password
     */
    public function setService($service)
    {
        if($this->service!==null && in_array($this,$this->service->getPasswords())) {
            $this->service->removePassword($this);
        }
        
        $this->service = $service;
        
        if(!in_array($this,$this->service->getPasswords())) {
            $this->service->addPassword($this);
        }
        
        return $this;
    }
 
 
 
} 
