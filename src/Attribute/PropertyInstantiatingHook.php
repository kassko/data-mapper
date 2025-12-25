<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class PropertyInstantiatingHook
{
    public function __construct(
        public readonly string $after_instantiating = '', // Method to call after object instantiation
        public readonly ?string $class = null,            // Optional: external class/service to call
        public readonly array $args = [],                 // Arguments to pass (supports ##object, expr())
    ) {}
}
