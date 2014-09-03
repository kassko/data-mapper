<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target({"PROPERTY","ANNOTATION"})
*
* @author kko
*/
final class Column
{
    use ColumnCommonTrait;

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