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

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class PropertySettingHook
{
    public function __construct(
        public readonly string $before_set_property = '',  // Method to call before setting property
        public readonly string $after_set_property = '',   // Method to call after setting property
        public readonly ?string $class = null,             // Optional: external class/service to call
        public readonly array $args = [],                  // Arguments to pass (supports ##object, #property, expr())
    ) {}
}
