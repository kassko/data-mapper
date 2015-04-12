<?php

namespace Kassko\DataMapper\Annotation;

/**
* @author kko
*/
trait MethodTrait
{
	/**
     * @var string
     */
    public $class = '##this';

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $args = [];
}
