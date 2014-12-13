<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class ToManyProvider
{
    use AssociationCommonTrait;

    /**
     * @var string
     */
    public $name;
}