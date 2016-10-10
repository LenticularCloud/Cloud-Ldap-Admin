<?php
namespace Cloud\LdapBundle\Entity\Ldap;


class Attribute
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @param $value    string
     */
    public function set($value)
    {
        $this->value = $value;
    }
}