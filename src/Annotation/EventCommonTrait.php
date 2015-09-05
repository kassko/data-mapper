<?php

namespace Kassko\DataMapper\Annotation;

/**
* Event annotation common attributes.
*
* @deprecated
* @see annotation Listeners
*
* @author kko
*/
trait EventCommonTrait
{
    /**
    * @var string
    *
    * This attribute is not used.
    */
    public $class;

    /**
    * @var string
    */
    public $method;
}
