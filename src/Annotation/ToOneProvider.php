<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class ToOneProvider
{
    use RelationProviderTrait;
}