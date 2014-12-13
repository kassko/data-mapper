<?php

namespace Kassko\DataMapper\Annotation;

/**
* Property annotations to be used in ToOneProvider annotations or ToManyProvider annotations.
*
* @author kko
*/
trait AssociationCommonTrait
{
	/**
     * @var string
     */
    public $entityClass;

    /**
     * @var string
     */
    public $repositoryClass;

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