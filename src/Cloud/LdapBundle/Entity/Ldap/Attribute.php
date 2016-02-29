<?php
namespace Cloud\LdapBundle\Entity\Ldap;


class Attribute
{
    private $value;

    public function __construct($value=null)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function set($value)
    {
        $this->value = $value;
    }
}