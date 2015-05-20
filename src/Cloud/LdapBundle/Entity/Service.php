<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use \Cloud\LdapBundle\Entity\Password;

class Service
{

    /**
     * name of the service
     *
     * @Assert\NotBlank()
     * 
     * @var String $name
     */
    private $name;

    /**
     * passwords for this service
     *
     * @Assert\Valid(deep=true)
     *
     * @var Array<Password> $passwords
     */
    private $passwords = array();

    /**
     * 
     * @param string $name
     */
    public function __construct($name){
        $this->name=$name;
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
        if (!isset($this->passwords[$passwordId]))
            return null;
        
        return $this->passwords[$passwordId];
    }

    /**
     *
     * @param Password $password            
     * @return \Cloud\LdapBundle\Entity\Service
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
}
