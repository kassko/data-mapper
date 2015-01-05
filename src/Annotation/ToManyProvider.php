<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class ToManyProvider
{
    use RelationProviderTrait;

    /**
     * @var string
     */
    public $name;
}