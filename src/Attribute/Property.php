<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Property
{
    public function __construct(
        public readonly ?string $name = null,      // Key name in data array
        public readonly ?string $class = null,     // Class for nested object hydration
        public readonly ?string $expand = null,    // Comma-separated props to expand
        public readonly ?string $noExpand = null,  // Comma-separated props to NOT expand
    ) {}
}
