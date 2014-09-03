<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
final class ToOne
{
    use AssociationCommonTrait;
}