<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropertyCandidate
{
    public function __construct(
        public readonly string $discriminator,   // Expression to evaluate (returns bool)
        public readonly Property $property,      // Property config to use if discriminator is true
    ) {}
}
