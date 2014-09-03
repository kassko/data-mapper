<?php

namespace Kassko\DataAccess\Annotation;

/**
* Property annotations to be used in ToOne annotations or ToMany annotations.
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
     * La stratégie de récupération à utiliser pour cette association
     *
     * @var bool
     */
    public $lazyFetching = false;
}