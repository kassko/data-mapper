<?php

namespace Kassko\DataMapper\Annotation;

/**
* Property annotations to be used in Column annotation or Entity annotation.
*
* A property used in Entity annotation works for all properties.
* A property used in Column annotation only works for the annotated property.
* If a property used in both, the Column annotation is priority.
*
* @author kko
*/
trait SourceCommonTrait
{
	/**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $args = [];

    /**
     * Loading strategy to use for this provider.
     *
     * @var bool
     */
    public $lazyLoading = false;

    /**
     * How so data contains data for one or severals fields.
     *
     * @var bool
     */
    public $supplySeveralFields = false;
}
