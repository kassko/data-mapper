<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target({"PROPERTY","ANNOTATION"})
*
* @author kko
*/
final class Getter
{
    /**
    * @var string
    */
    public $type = 'get';

    /**
    * @var string
    */
    public $name;
}