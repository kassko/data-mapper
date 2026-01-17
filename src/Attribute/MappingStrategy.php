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

use Attribute;
use Kassko\DataMapper\Enum\MappingStrategyPreset;
use Kassko\DataMapper\Exception\MappingStrategyException;

/**
 * Defines a mapping strategy for converting source field names to property names.
 * 
 * This attribute can be applied at:
 * - Class level: applies to all properties (unless overridden at property level)
 * - Property level: overrides class-level strategy for this specific property
 * 
 * The target property names are always expected to be in camelCase.
 * 
 * Preset Strategies:
 * - from_common_cases_mix (default): handles mixed cases (underscore, dash, camelCase, mixes)
 * - from_camel_case: source is already camelCase
 * - from_underscore_case: source uses underscores (first_name)
 * - from_dash_case: source uses dashes (first-name)
 * - from_pascal_case: source uses PascalCase (FirstName)
 * - from_snake_case: source uses Snake_Case (First_Name)
 * - from_constant_case: source uses CONSTANT_CASE (FIRST_NAME)
 * - from_upper_dash_case: source uses UPPER-DASH-CASE (FIRST-NAME)
 * 
 * Custom Strategies:
 * Use a callable to define custom mapping logic. The callable receives the source
 * field name and returns the target property name.
 * 
 * Examples:
 * ```php
 * // Class-level preset
 * #[MappingStrategy(preset: MappingStrategyPreset::FROM_UNDERSCORE_CASE)]
 * class Person { ... }
 * 
 * // Property-level override
 * class Person {
 *     #[MappingStrategy(preset: MappingStrategyPreset::FROM_DASH_CASE)]
 *     private string $lastName;
 * }
 * 
 * // Custom callable
 * #[MappingStrategy(custom: [MyMapper::class, 'mapField'])]
 * class Person { ... }
 * ```
 * 
 * Validation Rules:
 * - preset and custom are mutually exclusive
 * - Either preset or custom must be defined (unless enabled=false)
 * - Property::sourceField and MappingStrategy on the same property are mutually exclusive
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class MappingStrategy
{
    /**
     * @param MappingStrategyPreset|string|null $preset Predefined mapping strategy preset
     * @param array|callable|null $custom Custom callable for mapping [class, method] or closure
     * @param array $args Additional arguments to pass to the custom callable
     * @param bool $cascade Whether this attribute cascades to child classes
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(
        public readonly MappingStrategyPreset|string|null $preset = null,
        public readonly array|null $custom = null,
        public readonly array $args = [],
        public readonly bool $cascade = true,
        public readonly bool $enabled = true,
    ) {
        // Skip validation if disabled
        if (!$enabled) {
            return;
        }

        // Validate: preset and custom are mutually exclusive
        if ($preset !== null && $custom !== null) {
            throw MappingStrategyException::presetAndCustomAreMutuallyExclusive(
                'Both preset and custom were provided.'
            );
        }

        // Validate: at least one must be defined when enabled
        if ($preset === null && $custom === null) {
            throw MappingStrategyException::presetOrCustomRequired(
                'No mapping strategy specified.'
            );
        }

        // Validate preset if it's a string
        if (is_string($preset) && MappingStrategyPreset::tryFrom($preset) === null) {
            throw MappingStrategyException::invalidPreset($preset);
        }
    }

    /**
     * Get the preset as an enum value.
     */
    public function getPresetEnum(): ?MappingStrategyPreset
    {
        if ($this->preset === null) {
            return null;
        }

        if ($this->preset instanceof MappingStrategyPreset) {
            return $this->preset;
        }

        return MappingStrategyPreset::tryFrom($this->preset);
    }

    /**
     * Check if this strategy uses a preset.
     */
    public function hasPreset(): bool
    {
        return $this->preset !== null;
    }

    /**
     * Check if this strategy uses a custom callable.
     */
    public function hasCustom(): bool
    {
        return $this->custom !== null;
    }
}
