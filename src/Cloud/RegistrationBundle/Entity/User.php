<?php
namespace Cloud\RegistrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2,minMessage="Username must be at least {{ limit }} characters long")
     * @Assert\Regex("/^[a-zA-Z0-9_.-]+$/")
     */
    protected $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=6,minMessage="Password must be at least {{ limit }} characters long")
     */
    protected $password;

    /**
     * @ORM\Column(type="string",length=200)
     */
    protected $passwordHash;

    /**
     * @Assert\Email()
     * @ORM\Column(type="string",length=100,nullable=true)
     */
    protected $altEmail;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createAt;

    public function __construct()
    {
        $this->createAt=new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     * @return User
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }



    /**
     * @return string
     */
    public function getAltEmail()
    {
        return $this->altEmail;
    }

    /**
     * @param string $altEmail
     * @return User
     */
    public function setAltEmail($altEmail)
    {
        $this->altEmail = $altEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * @param \DateTime $createAt
     * @return User
     */
    public function setCreateAt(\DateTime $createAt)
    {
        $this->createAt = $createAt;
        return $this;
    }
}