<?php
namespace Cloud\LdapBundle\Entity\Ldap;



class AbstractAttribute
{

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param Attribute $attribute
     * @return AbstractAttribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function __construct()
    {
        $this->attribute=new Attribute();
    }


}