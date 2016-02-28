<?php
namespace Cloud\LdapBundle\Mapper;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Attribute implements Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var mixed
     */
    public $type = 'string';

    /**
     * @var integer
     */
    public $length;

    /**
     * The precision for a decimal (exact numeric) column (Applies only for decimal column).
     *
     * @var integer
     */
    public $precision = 0;

    /**
     * The scale for a decimal (exact numeric) column (Applies only for decimal column).
     *
     * @var integer
     */
    public $scale = 0;

    /**
     * @var boolean
     */
    public $unique = false;

    /**
     * @var boolean
     */
    public $nullable = false;

    /**
     * @var array
     */
    public $options = array();

    /**
     * @var string
     */
    public $columnDefinition;
}