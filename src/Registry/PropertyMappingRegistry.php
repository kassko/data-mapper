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

namespace Kassko\DataMapper\Registry;

use WeakMap;

/**
 * Registry for bidirectional property-to-sourceField mapping.
 * 
 * This registry uses WeakMap to track the correspondence between
 * property names and source field names (raw data keys) for each object.
 * This allows mapping in both directions:
 * - property -> sourceField (for extracting values from raw data)
 * - sourceField -> property (for knowing which property corresponds to a raw data key)
 * 
 * The mapping is automatically cleaned up when objects are garbage collected.
 */
final class PropertyMappingRegistry
{
    /**
     * Maps: object -> ['propertyToSource' => [propertyName => sourceField], 'sourceToProperty' => [sourceField => propertyName]]
     * @var WeakMap<object, array{propertyToSource: array<string, string>, sourceToProperty: array<string, string>}>
     */
    private static ?WeakMap $mappings = null;

    private static function getMap(): WeakMap
    {
        if (self::$mappings === null) {
            self::$mappings = new WeakMap();
        }
        return self::$mappings;
    }

    /**
     * Register a property-to-sourceField mapping for an object.
     * 
     * @param object $object The object being hydrated
     * @param string $propertyName The property name in the PHP object
     * @param string $sourceField The field name in the raw data (array key or DTO property)
     */
    public static function register(object $object, string $propertyName, string $sourceField): void
    {
        $map = self::getMap();
        
        if (!isset($map[$object])) {
            $map[$object] = [
                'propertyToSource' => [],
                'sourceToProperty' => [],
            ];
        }
        
        $current = $map[$object];
        $current['propertyToSource'][$propertyName] = $sourceField;
        $current['sourceToProperty'][$sourceField] = $propertyName;
        $map[$object] = $current;
    }

    /**
     * Get the source field name for a property.
     * 
     * @param object $object The object to look up
     * @param string $propertyName The property name to look up
     * @return string|null The source field name, or null if not registered
     */
    public static function getSourceField(object $object, string $propertyName): ?string
    {
        $map = self::getMap();
        return $map[$object]['propertyToSource'][$propertyName] ?? null;
    }

    /**
     * Get the property name for a source field.
     * 
     * @param object $object The object to look up
     * @param string $sourceField The source field name to look up
     * @return string|null The property name, or null if not registered
     */
    public static function getPropertyName(object $object, string $sourceField): ?string
    {
        $map = self::getMap();
        return $map[$object]['sourceToProperty'][$sourceField] ?? null;
    }

    /**
     * Check if a mapping exists for a property.
     * 
     * @param object $object The object to check
     * @param string $propertyName The property name to check
     * @return bool True if mapping exists
     */
    public static function hasPropertyMapping(object $object, string $propertyName): bool
    {
        $map = self::getMap();
        return isset($map[$object]['propertyToSource'][$propertyName]);
    }

    /**
     * Check if a mapping exists for a source field.
     * 
     * @param object $object The object to check
     * @param string $sourceField The source field name to check
     * @return bool True if mapping exists
     */
    public static function hasSourceFieldMapping(object $object, string $sourceField): bool
    {
        $map = self::getMap();
        return isset($map[$object]['sourceToProperty'][$sourceField]);
    }

    /**
     * Get all property-to-source mappings for an object.
     * 
     * @param object $object The object to look up
     * @return array<string, string> Property name => source field name
     */
    public static function getAllMappings(object $object): array
    {
        $map = self::getMap();
        return $map[$object]['propertyToSource'] ?? [];
    }

    /**
     * Get all source-to-property mappings for an object.
     * 
     * @param object $object The object to look up
     * @return array<string, string> Source field name => property name
     */
    public static function getAllReverseMappings(object $object): array
    {
        $map = self::getMap();
        return $map[$object]['sourceToProperty'] ?? [];
    }

    /**
     * Clear all mappings for an object.
     * 
     * @param object $object The object to clear
     */
    public static function clear(object $object): void
    {
        $map = self::getMap();
        if (isset($map[$object])) {
            unset($map[$object]);
        }
    }

    /**
     * Reset the entire registry (mainly for testing).
     */
    public static function reset(): void
    {
        self::$mappings = new WeakMap();
    }
}
