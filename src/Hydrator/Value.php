<?php

namespace Kassko\DataAccess\Hydrator;

/**
 * Contains a value to hydrate or to extract with an hydration strategy.
 *
 * @author kko
 */
class Value
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
