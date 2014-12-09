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
    * @var mixed
    */
    public $type = 'string';

    /**
    * @var string
    */
    public $readStrategy;

    /**
    * @var string
    */
    public $writeStrategy;
}