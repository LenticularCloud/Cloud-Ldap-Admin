<?php
namespace Cloud\LdapBundle\Util\Annotation;

use Cloud\LdapBundle\Schemas;

class AnnotationIndex
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

    public static function getSchemas()
    {
        $schemas=[];
        foreach(self::$schemas as $schema){
            $schemas[strtolower($schema)]=Schemas::class.'\\'.$schema;
        }
        return $schemas;
    }
}