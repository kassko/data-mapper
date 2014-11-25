<?php

namespace Kassko\DataAccess\Annotation;

/**
* Property annotations to be used in ToOne annotations or ToMany annotations.
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
