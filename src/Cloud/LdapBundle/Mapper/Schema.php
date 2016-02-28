<?php
namespace Cloud\LdapBundle\Mapper;


/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Schema
{
    /**
     * @var string
     */
    public $name=null;

}