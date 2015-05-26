<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target({"PROPERTY","ANNOTATION"})
*
* @author kko
*/
final class Field
{
    use FieldCommonTrait;

    /**
    * @var string
    */
    public $name;

    /**
    * @var string
    */
    public $type = null;

    /**
    * @var string
    */
    public $class = null;

    /**
    * @var string
    */
    public $defaultValue = null;

    /**
    * @var string
    */
    public $readConverter;

    /**
    * @var string
    */
    public $writeConverter;
}