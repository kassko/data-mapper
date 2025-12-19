<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Specifies properties that must be loaded before the current property.
 * Properties are loaded in the order they appear in the list.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Needs
{
    /**
     * @param string[] $properties List of property names to load first
     */
    public function __construct(
        public readonly array $properties = [],
    ) {}
}
