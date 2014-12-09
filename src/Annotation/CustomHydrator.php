<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class CustomHydrator
{
    /**
     * @var string
     *
     * The hydrator FQCN
     */
    public $class;

    /**
     * @var string
     *
     * @example To hydrate an object $object from raw result $data: hydrate($data, $object)
     */
    public $hydrateMethod = 'hydrate';

    /**
     * @var string
     * @example To extract data from an object $object: extract($object)
     */
    public $extractMethod = 'extract';
}