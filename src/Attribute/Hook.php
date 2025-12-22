<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Hook
{
    public const AFTER_CREATE_OBJECT = 'after_create_object';
    public const BEFORE_SET_PROPERTY = 'before_set_property';
    public const AFTER_SET_PROPERTY = 'after_set_property';

    public function __construct(
        public readonly string $name,           // Hook name (after_create_object, before_set_property, after_set_property)
        public readonly string $method = '',    // Method to call on the object or a service
        public readonly ?string $class = null,  // Optional: external class/service to call
        public readonly array $args = [],       // Arguments to pass (supports ##object, #property, expr())
    ) {}
}
