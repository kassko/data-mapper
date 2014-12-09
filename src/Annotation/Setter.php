<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target({"PROPERTY","ANNOTATION"})
*
* @author kko
*/
final class Setter
{
    /**
    * @var string
    */
    public $type = 'set';

    /**
    * @var string
    */
    public $name;
}