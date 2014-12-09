<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class Provider
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
     * Loading strategy to use for this provider.
     *
     * @var bool
     */
    public $lazyLoading = false;
}