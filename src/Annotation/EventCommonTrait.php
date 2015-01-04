<?php

namespace Kassko\DataMapper\Annotation;

/**
* Property annotations to be used in ToOneDataSource annotations or ToManyDataSource annotations.
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
