<?php

namespace Kassko\DataMapper\Annotation;

/**
* Property annotations to be used in ToOneProvider annotations or ToManyProvider annotations.
*
* @author kko
*/
trait EventCommonTrait
{
    /**
    * @var string
    */
    public $class;

    /**
    * @var string
    */
    public $method;
}
