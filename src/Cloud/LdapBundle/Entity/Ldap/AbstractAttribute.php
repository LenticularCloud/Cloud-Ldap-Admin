<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 2/27/16
 * Time: 7:18 PM
 */

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


}