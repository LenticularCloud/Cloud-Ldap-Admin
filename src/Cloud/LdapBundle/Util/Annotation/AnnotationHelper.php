<?php
namespace Cloud\LdapBundle\Util\Annotation;

use Cloud\LdapBundle\Schemas;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationHelper
{

    private static $schemas = [
        'GroupOfNames',
        'InetOrgPerson',
        'OrganizationalPerson',
        'Person',
        'PosixAccount',
        'PosixGroup',
        'SambaGroupMapping',
        'SambaSamAccount',
        'ShadowAccount',
    ];

    /**
     * @var AnnotationReader
     */
    private static $reader = null;

    public static function getSchemas()
    {
        $schemas=[];
        foreach(self::$schemas as $schema){
            $schemas[strtolower($schema)]=Schemas::class.'\\'.$schema;
        }
        return $schemas;
    }


    public static function getReader() {

        if( self::$reader === null) {
            self::$reader = new AnnotationReader();
        }
        return self::$reader;
    }
}