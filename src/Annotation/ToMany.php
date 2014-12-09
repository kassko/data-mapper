<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class ToMany
{
    use AssociationCommonTrait;

    /**
     * @var string
     */
    public $name;
}