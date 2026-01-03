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

namespace Kassko\Sample\PropertyInstantiatingHook;

/**
 * External service that tracks entity instantiation.
 */
class InstantiationTracker
{
    private static array $trackedEntities = [];

    public function trackInstantiation(object $entity): void
    {
        self::$trackedEntities[] = [
            'class' => get_class($entity),
            'id' => method_exists($entity, 'getId') ? $entity->getId() : null,
            'timestamp' => microtime(true),
        ];
    }

    public static function getTrackedEntities(): array
    {
        return self::$trackedEntities;
    }

    public static function getTrackedCount(): int
    {
        return count(self::$trackedEntities);
    }

    public static function reset(): void
    {
        self::$trackedEntities = [];
    }
}
