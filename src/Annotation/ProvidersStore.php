<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class ProvidersStore
{
    /**
     * One or more Provider annotations.
     *
     * @var array<\Kassko\DataMapper\Annotation\Provider>
     */
    public $items = [];
}
