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

namespace Kassko\DataMapper\Loader;

use Kassko\DataMapper\Enum\MappingStrategyPreset;

/**
 * Utility class for converting between different string cases.
 * 
 * Converts source field names (various cases) to target property names (camelCase).
 * 
 * Supported conversions:
 * - underscore_case -> camelCase
 * - dash-case -> camelCase  
 * - PascalCase -> camelCase
 * - Snake_Case -> camelCase
 * - CONSTANT_CASE -> camelCase
 * - UPPER-DASH-CASE -> camelCase
 * - Mixed cases (combinations) -> camelCase
 */
final class CaseConverter
{
    /**
     * Convert a source field name to camelCase using the specified preset strategy.
     * 
     * @param string $sourceField The source field name to convert
     * @param MappingStrategyPreset $preset The preset strategy to use
     * @return string The converted field name in camelCase
     */
    public static function convert(string $sourceField, MappingStrategyPreset $preset): string
    {
        return match ($preset) {
            MappingStrategyPreset::FROM_COMMON_CASES_MIX => self::fromCommonCasesMix($sourceField),
            MappingStrategyPreset::FROM_CAMEL_CASE => $sourceField, // Already camelCase
            MappingStrategyPreset::FROM_UNDERSCORE_CASE => self::fromUnderscoreCase($sourceField),
            MappingStrategyPreset::FROM_DASH_CASE => self::fromDashCase($sourceField),
            MappingStrategyPreset::FROM_PASCAL_CASE => self::fromPascalCase($sourceField),
            MappingStrategyPreset::FROM_SNAKE_CASE => self::fromSnakeCase($sourceField),
            MappingStrategyPreset::FROM_CONSTANT_CASE => self::fromConstantCase($sourceField),
            MappingStrategyPreset::FROM_UPPER_DASH_CASE => self::fromUpperDashCase($sourceField),
        };
    }

    /**
     * Convert from camelCase property name to source field name using the specified preset.
     * This is the reverse operation: property -> source field.
     * 
     * @param string $propertyName The camelCase property name
     * @param MappingStrategyPreset $preset The preset strategy (determines target case)
     * @return string The source field name in the target case
     */
    public static function convertReverse(string $propertyName, MappingStrategyPreset $preset): string
    {
        return match ($preset) {
            MappingStrategyPreset::FROM_COMMON_CASES_MIX => self::toUnderscoreCase($propertyName), // Default to underscore
            MappingStrategyPreset::FROM_CAMEL_CASE => $propertyName,
            MappingStrategyPreset::FROM_UNDERSCORE_CASE => self::toUnderscoreCase($propertyName),
            MappingStrategyPreset::FROM_DASH_CASE => self::toDashCase($propertyName),
            MappingStrategyPreset::FROM_PASCAL_CASE => self::toPascalCase($propertyName),
            MappingStrategyPreset::FROM_SNAKE_CASE => self::toSnakeCase($propertyName),
            MappingStrategyPreset::FROM_CONSTANT_CASE => self::toConstantCase($propertyName),
            MappingStrategyPreset::FROM_UPPER_DASH_CASE => self::toUpperDashCase($propertyName),
        };
    }

    /**
     * Try to find a matching source field in the data using the specified preset.
     * If no match is found using the preset, fallback to property name.
     * 
     * @param string $propertyName The camelCase property name
     * @param array<string, mixed> $data The data to search in
     * @param MappingStrategyPreset $preset The preset strategy to use
     * @return string The matching source field name
     */
    public static function findSourceField(string $propertyName, array $data, MappingStrategyPreset $preset): string
    {
        // For common cases mix, try multiple variations
        if ($preset === MappingStrategyPreset::FROM_COMMON_CASES_MIX) {
            return self::findSourceFieldForCommonCases($propertyName, $data);
        }

        // Convert property name to expected source field format
        $expectedSourceField = self::convertReverse($propertyName, $preset);
        
        // Check if it exists in data
        if (array_key_exists($expectedSourceField, $data)) {
            return $expectedSourceField;
        }

        // Fallback to property name
        if (array_key_exists($propertyName, $data)) {
            return $propertyName;
        }

        // Return expected source field even if not found (may be resolved later)
        return $expectedSourceField;
    }

    /**
     * Find source field for common cases mix preset.
     * Tries multiple case variations to find a match.
     */
    private static function findSourceFieldForCommonCases(string $propertyName, array $data): string
    {
        // 1. Try exact property name (already camelCase)
        if (array_key_exists($propertyName, $data)) {
            return $propertyName;
        }

        // 2. Try underscore_case
        $underscoreCase = self::toUnderscoreCase($propertyName);
        if (array_key_exists($underscoreCase, $data)) {
            return $underscoreCase;
        }

        // 3. Try dash-case
        $dashCase = self::toDashCase($propertyName);
        if (array_key_exists($dashCase, $data)) {
            return $dashCase;
        }

        // 4. Try PascalCase
        $pascalCase = self::toPascalCase($propertyName);
        if (array_key_exists($pascalCase, $data)) {
            return $pascalCase;
        }

        // 5. Try CONSTANT_CASE
        $constantCase = self::toConstantCase($propertyName);
        if (array_key_exists($constantCase, $data)) {
            return $constantCase;
        }

        // 6. Try UPPER-DASH-CASE
        $upperDashCase = self::toUpperDashCase($propertyName);
        if (array_key_exists($upperDashCase, $data)) {
            return $upperDashCase;
        }

        // 7. Try mixed dash-underscore patterns
        $mixedPatterns = self::generateMixedPatterns($propertyName);
        foreach ($mixedPatterns as $pattern) {
            if (array_key_exists($pattern, $data)) {
                return $pattern;
            }
        }

        // Fallback to underscore_case (most common convention)
        return $underscoreCase;
    }

    /**
     * Generate mixed dash-underscore patterns for a property name.
     */
    private static function generateMixedPatterns(string $propertyName): array
    {
        $patterns = [];
        $words = self::splitCamelCase($propertyName);
        
        if (count($words) < 2) {
            return $patterns;
        }

        // Generate patterns like: first-second_third, first_second-third
        $separators = ['-', '_'];
        $numWords = count($words);
        
        // Only generate a few common patterns to avoid explosion
        // Pattern: dash between first words, underscore for the rest
        if ($numWords >= 2) {
            $patterns[] = strtolower($words[0]) . '-' . strtolower(implode('_', array_slice($words, 1)));
            $patterns[] = strtolower($words[0]) . '_' . strtolower(implode('-', array_slice($words, 1)));
        }

        return $patterns;
    }

    // ========================================================================
    // TO CAMELCASE CONVERSIONS (source -> property)
    // ========================================================================

    /**
     * Convert from mixed cases to camelCase.
     * Handles: underscore_case, dash-case, PascalCase, mixes
     */
    public static function fromCommonCasesMix(string $input): string
    {
        // First normalize: replace dashes with underscores
        $normalized = str_replace('-', '_', $input);
        
        // Then convert from underscore/mixed case to camelCase
        $result = self::fromUnderscoreCase($normalized);
        
        // Handle PascalCase - ensure first letter is lowercase
        return lcfirst($result);
    }

    /**
     * Convert from underscore_case to camelCase.
     * Example: first_name -> firstName
     */
    public static function fromUnderscoreCase(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords(strtolower($input), '_')));
    }

    /**
     * Convert from dash-case to camelCase.
     * Example: first-name -> firstName
     */
    public static function fromDashCase(string $input): string
    {
        return lcfirst(str_replace('-', '', ucwords(strtolower($input), '-')));
    }

    /**
     * Convert from PascalCase to camelCase.
     * Example: FirstName -> firstName
     */
    public static function fromPascalCase(string $input): string
    {
        return lcfirst($input);
    }

    /**
     * Convert from Snake_Case to camelCase.
     * Example: First_Name -> firstName
     */
    public static function fromSnakeCase(string $input): string
    {
        // First convert to lowercase with underscores
        $parts = explode('_', $input);
        $parts = array_map('strtolower', $parts);
        return lcfirst(implode('', array_map('ucfirst', $parts)));
    }

    /**
     * Convert from CONSTANT_CASE to camelCase.
     * Example: FIRST_NAME -> firstName
     */
    public static function fromConstantCase(string $input): string
    {
        return self::fromUnderscoreCase(strtolower($input));
    }

    /**
     * Convert from UPPER-DASH-CASE to camelCase.
     * Example: FIRST-NAME -> firstName
     */
    public static function fromUpperDashCase(string $input): string
    {
        return self::fromDashCase(strtolower($input));
    }

    // ========================================================================
    // FROM CAMELCASE CONVERSIONS (property -> source)
    // ========================================================================

    /**
     * Convert from camelCase to underscore_case.
     * Example: firstName -> first_name
     */
    public static function toUnderscoreCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Convert from camelCase to dash-case.
     * Example: firstName -> first-name
     */
    public static function toDashCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $input));
    }

    /**
     * Convert from camelCase to PascalCase.
     * Example: firstName -> FirstName
     */
    public static function toPascalCase(string $input): string
    {
        return ucfirst($input);
    }

    /**
     * Convert from camelCase to Snake_Case.
     * Example: firstName -> First_Name
     */
    public static function toSnakeCase(string $input): string
    {
        $underscored = preg_replace('/(?<!^)[A-Z]/', '_$0', $input);
        return ucwords($underscored, '_');
    }

    /**
     * Convert from camelCase to CONSTANT_CASE.
     * Example: firstName -> FIRST_NAME
     */
    public static function toConstantCase(string $input): string
    {
        return strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Convert from camelCase to UPPER-DASH-CASE.
     * Example: firstName -> FIRST-NAME
     */
    public static function toUpperDashCase(string $input): string
    {
        return strtoupper(preg_replace('/(?<!^)[A-Z]/', '-$0', $input));
    }

    /**
     * Split a camelCase string into words.
     */
    private static function splitCamelCase(string $input): array
    {
        return preg_split('/(?=[A-Z])/', $input, -1, PREG_SPLIT_NO_EMPTY);
    }
}
