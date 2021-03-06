<?php
namespace Cloud\LdapBundle\Entity\Ldap;


use Cloud\LdapBundle\Util\Annotation\AnnotationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Cloud\LdapBundle\Mapper;

abstract class AbstractEntity
{
    /**
     * @var ArrayCollection
     */
    protected $objects;

    /**
     * @var ArrayCollection
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $dn = null;

    /**
     *
     */
    protected $modifyTimestamp;

    /**
     *
     */
    protected $createtimestamp;

    public function __construct()
    {
        $this->objects = new ArrayCollection();
        $this->attributes = new ArrayCollection();

    }

    public function getDn()
    {
        return $this->dn;
    }

    public function setDn($dn)
    {
        $this->dn = $dn;

        return $this;
    }

    public function getObject($type)
    {
        if (!$this->objects instanceof ArrayCollection) {
            return null;
        }
        foreach ($this->objects as $object) {
            if ($object instanceof $type) {
                return $object;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    abstract public function getObjectClasses();

    protected function afterAddObject($class)
    {
    }

    public function addObject($class, $ldapArray = [])
    {
        $object = new $class();
        $reflectionObject = new \ReflectionObject($object);
        do {
            foreach ($reflectionObject->getProperties() as $reflectionProperty) {
                $annotation = AnnotationHelper::getReader()->getPropertyAnnotation($reflectionProperty,
                    Mapper\Attribute::class);
                if ($annotation instanceof Mapper\Attribute) {
                    $name = strtolower(isset($annotation->name) ? $annotation->name : $reflectionProperty->name);


                    if ($this->attributes->containsKey($name)) {
                        $reflectionProperty->setAccessible(true);
                        $reflectionProperty->setValue($object, $this->attributes[$name]);
                        continue;
                    }

                    $attribute = null;
                    switch ($annotation->type) {
                        case 'string':
                        case 'number':
                            if (isset($ldapArray[$name])) {
                                if ($this->attributes->containsKey($name)) {
                                    $attribute = $this->attributes[$name];
                                } else {
                                    if (is_array($ldapArray[$name])) {
                                        $value = isset($ldapArray[$name][0]) ? $ldapArray[$name][0] : null;
                                    } else {
                                        $value = $ldapArray[$name];
                                    }

                                    $attribute = new Attribute();
                                    $attribute->set($value);
                                }
                            } else {
                                $attribute = new Attribute();
                            }
                            break;
                        case 'array':
                            if (isset($ldapArray[$name])) {
                                if (!is_array($ldapArray[$name])) {
                                    $values = array($ldapArray[$name]);
                                } else {
                                    $values = [];
                                    foreach ($ldapArray[$name] as $key => $value) {
                                        if ($key === 'count') {
                                            continue;
                                        }
                                        $values[] = new Attribute($value);
                                    }
                                }

                                $attribute = new ArrayCollection($values);
                            } else {
                                $attribute = new ArrayCollection();
                            }
                            break;
                        case 'bool':
                        case 'boolean':
                            if (isset($ldapArray[$name])) {
                                if ($this->attributes->containsKey($name)) {
                                    $attribute = $this->attributes[$name];
                                } else {
                                    if (is_array($ldapArray[$name])) {
                                        $value = isset($ldapArray[$name][0]) ? $ldapArray[$name][0] : null;
                                    } else {
                                        $value = $ldapArray[$name];
                                    }

                                    $attribute = new Attribute();
                                    $attribute->set($value);
                                }
                            } else {
                                $attribute = new Attribute("FALSE");
                            }
                            break;
                        default:
                            throw new \InvalidArgumentException('Invalid Attribute Type :'.$annotation->type);
                    }
                    $this->attributes[$name] = $attribute;

                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($object, $attribute);
                }
            }
        } while ($reflectionObject = $reflectionObject->getParentClass());
        $this->objects[] = $object;
        $this->afterAddObject($class);
    }


    public function removeObject($class, $removeAttrs = [])
    {
        foreach ($removeAttrs as $attr) {
            $this->attributes[$attr]->set(null);
        }
        $this->objects->removeElement($this->getObject($class));
    }


    public function getModifyTimestamp()
    {
        return $this->modifyTimestamp;
    }


    public function setModifyTimestamp(\DateTime $modifyTimestamp)
    {

        $this->modifyTimestamp = $modifyTimestamp;
    }


    public function getCreateTimestamp()
    {
        return $this->createtimestamp;
    }

    public function setCreateTimestamp(\DateTime $createtimestamp)
    {
        $this->createtimestamp = $createtimestamp;
    }
}