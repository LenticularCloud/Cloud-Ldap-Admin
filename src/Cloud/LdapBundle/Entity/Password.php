<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\AbstractAttribute;
use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Password extends  AbstractAttribute
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
     * @Assert\Regex("/^[a-zA-Z0-9_-]+$/",message="Id have to be only chars from a-zA-Z0-9_-")
     * @Assert\Length(max=10,min=2,maxMessage="Id have to be max. 10 chars long",minMessage="Id have to min. 2 chars long")
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
     * @Assert\Valid(deep=true)
     * 
     * @var User    $user
     */
    private $user;

    /**
     * @var bool
     */
    private $masterPassword;

    /**
     * @var string classname
     */
    private $encoder;
    
    /**
     * @Assert\Valid(deep=true)
     * 
     * @var Service
     */
    private $service;

    public function __construct($id = null, $password_plain = null,$isMasterPassword=false)
    {
        parent::__construct();
        $this->password_plain = $password_plain;
        $this->id = $id;
        $this->masterPassword=$isMasterPassword;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($context->getGroup()=='create' && ! isset($this->hash) && ! isset($this->password_plain)) {
            $context->buildViolation('password_plain have to be not null if no hash is set')
                ->atPath('password_plain')
                ->addViolation();
        }
    }

    /**
     *
     * @return String
     */
    public function getHash()
    {
        return $this->getAttribute()->get();
    }

    /**
     *
     * @param $hash
     */
    public function setHash($hash)
    {
        $this->getAttribute()->set($hash);
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * unique
     * allow /(a-Z0-9_-){1,10}/
     *
     * @param string $id
     * @return Password
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
     * @return String
     */
    public function getPasswordPlain()
    {
        return $this->password_plain;
    }

    /**
     *
     * @param $password_plain
     */
    public function setPasswordPlain($password_plain)
    {
        $this->password_plain = $password_plain;
        return $this;
    }

    public function isMasterPassword()
    {
        return $this->masterPassword;
    }

    public function setMasterPassword($isMasterPassword)
    {
        $this->masterPassword = $isMasterPassword;
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

    /**
     * @return string
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param string $encoder
     * @return Password
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }

    public function __clone()
    {
        $hash=$this->getHash();
        $this->setAttribute(new Attribute());
        $this->getAttribute()->set($hash);
    }
} 
