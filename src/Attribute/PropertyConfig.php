<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Attribute;

use Kassko\DataMapper\Enum\SensitiveLevel;

/**
 * PropertyConfig defines a reusable property configuration with an ID.
 * Used inside PropertyConfigStore at class level.
 */
final class PropertyConfig
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $class = null,        // Class for single object hydration (container type)
        public readonly ?string $itemClass = null,    // Class for collection item hydration (element type)
        public readonly ?string $sourceField = null,  // Field name in raw data (array key or DTO property)
        public readonly ?string $expand = null,
        public readonly ?string $noExpand = null,
        public readonly ?array $mapping = null,
        public readonly ?SensitiveLevel $sensitiveLevel = null,  // Sensitivity level for lineage collection
        public readonly bool $cascade = true,                    // Whether this config cascades to child classes
        public readonly bool $enabled = true,                    // Whether this config is active (disabled configs are ignored)
    ) {
        // Validation: mapping requires class or itemClass to be set
        if ($mapping !== null && $class === null && $itemClass === null) {
            throw new \InvalidArgumentException('PropertyConfig: mapping can only be set when class or itemClass is also specified');
        }
    }
}
