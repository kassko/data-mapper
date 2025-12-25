<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

/**
 * PropertyCandidate is used ONLY inside PropertyCandidates attribute.
 * It cannot be used directly on a property.
 */
final class PropertyCandidate
{
    public function __construct(
        public readonly string $discriminator,   // Expression to evaluate (returns bool)
        public readonly Property $property,      // Property config to use if discriminator is true
    ) {}
}
