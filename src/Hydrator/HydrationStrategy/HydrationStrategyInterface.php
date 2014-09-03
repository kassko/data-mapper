<?php

namespace Kassko\DataAccess\Hydrator\HydrationStrategy;

/**
* Contract for field hydration strategy.
*
* @author kko
*/
interface HydrationStrategyInterface
{
    /**
    * Converts the given value so that it can be extracted by the hydrator.
    *
    * @param mixed $value The original value.
    * @param array $data The whole data is optionally provided as context.
    * @return mixed Returns the value that should be extracted.
    */
    public function extract($value, $object = null, $data = null);

    /**
    * Converts the given value so that it can be hydrated by the hydrator.
    *
    * @param mixed $value The original value.
    * @param array $data The object is optionally provided as context.
    * @return mixed Returns the value that should be hydrated.
    */
    public function hydrate($value, $data = null, $object = null);
}