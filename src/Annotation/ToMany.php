<?php

namespace Kassko\DataAccess\Annotation;

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