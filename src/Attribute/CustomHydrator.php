<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class CustomHydrator
{
    public function __construct(
        public readonly string $key,             // Key to identify the custom hydrator
        public readonly ?string $objectClass = null,  // Optional: expected class for type checking
    ) {}
}
