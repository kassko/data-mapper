<?php

namespace Kassko\DataMapper\Annotation;

/**
* Property annotations to be used in ToOneDataSource annotations or ToManyDataSource annotations.
*
* @author kko
*/
trait RelationProviderTrait
{
    /**
     * @var string
     */
    public $class;

	/**
     * @var string
     */
    public $objectClass;

    /**
     * @var string
     */
    public $findMethod;

    /**
     * Loading strategy to use for this association.
     *
     * @var bool
     */
    public $lazyLoading = false;
}