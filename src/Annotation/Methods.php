<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("ANNOTATION")
*
* @author kko
*/
final class Methods
{
    /**
     * One or more Method annotations.
     *
     * @var array<\Kassko\DataMapper\Annotation\Method>
     */
    public $items = [];
}
