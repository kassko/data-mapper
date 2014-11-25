<?php

namespace Kassko\DataAccess\Hydrator\HydrationStrategy;

use Kassko\DataAccess\Hydrator\ImmutableHydrationContext;
use Kassko\DataAccess\Hydrator\Value;
use InvalidArgumentException;

/**
 * Strategy to hydrate a field.
 *
 * @author kko
 */
class ClosureHydrationStrategy implements HydrationStrategyInterface
{
    protected $extractFunc = null;
    protected $hydrateFunc = null;

    public function __construct($extractFunc = null, $hydrateFunc = null)
    {
        if (isset($extractFunc)) {
            if (! is_callable($extractFunc)) {
                throw new InvalidArgumentException('$extractFunc must be callable');
            }

            $this->extractFunc = $extractFunc;
        } else {
            $this->extractFunc = function ($value) {
                return $value;
            };
        }

        if (isset($hydrateFunc)) {
            if (! is_callable($hydrateFunc)) {
                throw new InvalidArgumentException('$hydrateFunc must be callable');
            }

            $this->hydrateFunc = $hydrateFunc;
        } else {
            $this->hydrateFunc = function ($value) {
                return $value;
            };
        }
    }

    /**
    * {@inheritdoc}
    */
    public function extract($value, $object = null, $data = null)
    {
        $this->extractFunc->__invoke($objValue = new Value($value), new ImmutableHydrationContext($data));

        return $objValue->value;
    }

    /**
    * {@inheritdoc}
    */
    public function hydrate($value, $data = null, $object = null)
    {
        $this->hydrateFunc->__invoke($objValue = new Value($value), new ImmutableHydrationContext($data));

        return $objValue->value;
    }
}
