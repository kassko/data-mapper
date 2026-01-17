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

namespace Kassko\DataMapper\Enum;

/**
 * Enum for mapping strategy presets.
 * 
 * Defines predefined strategies for converting source field names (various cases)
 * to target property names (always camelCase).
 * 
 * Reference: https://www.hashbangcode.com/snippets/different-types-cases-used-programming
 */
enum MappingStrategyPreset: string
{
    /**
     * Default: handles mixed cases (underscore, dash, camelCase, and mixes).
     * Example sources: first_name, last-name, billingAddress, home-delivery_address
     */
    case FROM_COMMON_CASES_MIX = 'from_common_cases_mix';

    /**
     * Source is in camelCase (already matches target).
     * Example: firstName -> firstName
     */
    case FROM_CAMEL_CASE = 'from_camel_case';

    /**
     * Source uses underscores with lowercase letters.
     * Example: first_name -> firstName
     */
    case FROM_UNDERSCORE_CASE = 'from_underscore_case';

    /**
     * Source uses dashes with lowercase letters.
     * Example: first-name -> firstName
     */
    case FROM_DASH_CASE = 'from_dash_case';

    /**
     * Source uses PascalCase (capitalized first letter).
     * Example: FirstName -> firstName
     */
    case FROM_PASCAL_CASE = 'from_pascal_case';

    /**
     * Source uses Snake_Case (capitalized words with underscores).
     * Example: First_Name -> firstName
     */
    case FROM_SNAKE_CASE = 'from_snake_case';

    /**
     * Source uses CONSTANT_CASE (uppercase with underscores).
     * Example: FIRST_NAME -> firstName
     */
    case FROM_CONSTANT_CASE = 'from_constant_case';

    /**
     * Source uses UPPER-DASH-CASE (uppercase with dashes).
     * Example: FIRST-NAME -> firstName
     */
    case FROM_UPPER_DASH_CASE = 'from_upper_dash_case';

    /**
     * Get the default preset for mapping strategies.
     */
    public static function default(): self
    {
        return self::FROM_COMMON_CASES_MIX;
    }

    /**
     * Create a preset from a string value.
     * 
     * @param string $value The string value of the preset
     * @return self|null The preset, or null if not found
     */
    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
