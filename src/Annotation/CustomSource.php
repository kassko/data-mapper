<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class CustomSource
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
     * La stratégie de récupération à utiliser pour cette association
     *
     * @var bool
     */
    public $lazyLoading = false;
}