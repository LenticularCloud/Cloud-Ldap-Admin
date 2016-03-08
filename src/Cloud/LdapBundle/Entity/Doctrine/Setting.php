<?php
namespace Cloud\LdapBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class Setting
{
    /**
     * @ORM\Column(type="string",length=30)
     * @ORM\Id
     */
    private $key;

    /**
     * @ORM\Column(type="string",length=150)
     */
    private $value;


    public function __construct($key)
    {
        $this->key=$key;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return Setting
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}