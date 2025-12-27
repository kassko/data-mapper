<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class PropertyHydratingHook
{
    public function __construct(
        public readonly string $before_hydrate_object = '',  // Method to call before hydration (receives array $rawData)
        public readonly string $after_hydrate_object = '',   // Method to call after hydration (receives ?object $object, array $rawData)
        public readonly ?string $class = null,               // Optional: external class/service to call
        public readonly array $args = [],                    // Arguments to pass (supports ##object, expr())
    ) {}
}
