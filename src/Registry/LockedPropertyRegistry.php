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

namespace Kassko\DataMapper\Registry;

use WeakMap;

/**
 * Registry for tracking locked properties.
 * 
 * This registry uses WeakMap to track which properties are locked for each object,
 * ensuring the locked state is not serialized with the object itself.
 */
final class LockedPropertyRegistry
{
    /** @var WeakMap<object, array<string, bool>> */
    private static ?WeakMap $lockedProperties = null;

    private static function getMap(): WeakMap
    {
        if (self::$lockedProperties === null) {
            self::$lockedProperties = new WeakMap();
        }
        return self::$lockedProperties;
    }

    /**
     * Lock a property to prevent lazy/eager loading from modifying its value.
     */
    public static function lock(object $object, string $propertyName): void
    {
        $map = self::getMap();
        if (!isset($map[$object])) {
            $map[$object] = [];
        }
        $map[$object][$propertyName] = true;
    }

    /**
     * Unlock a property to allow lazy/eager loading to modify its value.
     */
    public static function unlock(object $object, string $propertyName): void
    {
        $map = self::getMap();
        if (isset($map[$object])) {
            // WeakMap doesn't support indirect modification, need to get/modify/set
            $properties = $map[$object];
            unset($properties[$propertyName]);
            $map[$object] = $properties;
        }
    }

    /**
     * Check if a property is locked.
     */
    public static function isLocked(object $object, string $propertyName): bool
    {
        $map = self::getMap();
        return ($map[$object][$propertyName] ?? false);
    }

    /**
     * Get all locked properties for an object.
     * 
     * @return array<string, bool>
     */
    public static function getLockedProperties(object $object): array
    {
        $map = self::getMap();
        return $map[$object] ?? [];
    }

    /**
     * Clear all locked properties for an object.
     */
    public static function clearObject(object $object): void
    {
        $map = self::getMap();
        unset($map[$object]);
    }

    /**
     * Clear all locked properties (for testing purposes).
     */
    public static function clear(): void
    {
        self::$lockedProperties = new WeakMap();
    }
}
