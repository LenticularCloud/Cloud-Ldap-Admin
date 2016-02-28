<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Entity\Ldap\AbstractEntity;
use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper;
use Cloud\LdapBundle\Util\Annotation\AnnotationIndex;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Cloud\LdapBundle\Entity\Service;
use Cloud\LdapBundle\Schemas;

class LdapArrayToObjectTransformer
{

    private $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader();
    }

    public function transform(AbstractEntity $entity)
    {
        $data = [];
        $data["objectclass"]=[];
        foreach ($entity->getObjects() as $class=>$object) {
            $reflectionObject = new \ReflectionObject($object);
            $schema=$this->reader->getClassAnnotation($reflectionObject,Mapper\Schema::class);
            if($schema->name===null) {
                $name=$reflectionObject->getShortName();
            }else {
                $name=$schema->name;
            }
            $data["objectclass"][]=$name;
        }

        foreach($entity->getAttributes() as $key=>$attribute) {
            switch(get_class($attribute)){
                case Attribute::class:
                    if($attribute->get()!==null) {
                        $data[$key]=$attribute->get();
                    }
                    break;
                case ArrayCollection::class:
                    $data[$key]=[];
                    foreach($attribute as $_attribute) {
                        $data[$key][]=$_attribute->get();
                    }
                    if(count($data[$key]) === 0) {
                        unset($data[$key]);
                    }
                    break;
                default:
                    throw new \InvalidArgumentException();
            }
        }
        return $data;
    }

    /**
     * @param mixed $ldapArray
     * @param AbstractEntity $entity
     * @return AbstractEntity
     */
    public function reverseTransform($ldapArray,AbstractEntity $entity)
    {
        $attributes = new ArrayCollection();
        $schemaClasses=$entity->getObjectClasses();

        //force lower keynames
        $tmp=[];
        foreach($ldapArray as $key =>$value) {
            $tmp[strtolower($key)]=$value;
        }
        $ldapArray=$tmp;

        $objects=new ArrayCollection();
        foreach ($ldapArray["objectclass"] as $key => $objectClass) {
            if ($key === 'count') {
                continue;
            }

            if (isset($schemaClasses[strtolower($objectClass)])) {
                $class = $schemaClasses[strtolower($objectClass)];
                $entity->addObject($class,$ldapArray);
            }
        }
        return $entity;
    }

    static $var=0;
}
