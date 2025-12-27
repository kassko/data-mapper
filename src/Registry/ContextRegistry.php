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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Registry for context values used during hydration.
 * 
 * Context values can come from two sources:
 * 1. Application context: Set externally via addToContext() before hydration
 * 2. Hydration context: Set via #[Context] attributes during hydration
 * 
 * Values from hydration context can override application context values.
 * Context values accumulate as hydration descends into nested objects.
 */
final class ContextRegistry
{
    /** @var array<string, mixed> Application-level context */
    private static array $applicationContext = [];
    
    /** @var array<string, mixed> Hydration context (from #[Context] attributes) */
    private static array $hydrationContext = [];

    /** @var LoggerInterface */
    private static ?LoggerInterface $logger = null;

    /**
     * Set the logger for context access warnings.
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    private static function getLogger(): LoggerInterface
    {
        if (self::$logger === null) {
            self::$logger = new NullLogger();
        }
        return self::$logger;
    }

    /**
     * Add a value to the application context.
     * This context persists across hydration operations.
     */
    public static function addToApplicationContext(string $key, mixed $value): void
    {
        self::$applicationContext[$key] = $value;
    }

    /**
     * Add multiple values to the application context.
     * 
     * @param array<string, mixed> $values
     */
    public static function addManyToApplicationContext(array $values): void
    {
        foreach ($values as $key => $value) {
            self::$applicationContext[$key] = $value;
        }
    }

    /**
     * Set a hydration context value (from #[Context] attributes).
     */
    public static function set(string $key, mixed $value): void
    {
        self::$hydrationContext[$key] = $value;
    }

    /**
     * Get a context value. Hydration context takes precedence over application context.
     * If the key doesn't exist, logs a warning and returns null.
     * 
     * @param string $key
     * @param mixed $default Default value if key not found (avoids warning if provided)
     * @param bool $warnIfMissing Whether to log a warning if key is missing
     * @return mixed
     */
    public static function get(string $key, mixed $default = null, bool $warnIfMissing = true): mixed
    {
        // Hydration context takes precedence
        if (array_key_exists($key, self::$hydrationContext)) {
            return self::$hydrationContext[$key];
        }
        
        // Fall back to application context
        if (array_key_exists($key, self::$applicationContext)) {
            return self::$applicationContext[$key];
        }
        
        // Key not found
        if ($warnIfMissing && $default === null) {
            self::getLogger()->warning(
                'Accessing non-existent context key',
                ['key' => $key]
            );
        }
        
        return $default;
    }

    /**
     * Check if a context key exists (in either application or hydration context).
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$hydrationContext) 
            || array_key_exists($key, self::$applicationContext);
    }

    /**
     * Set multiple hydration context values at once.
     * 
     * @param array<string, mixed> $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * Clear hydration context only (preserves application context).
     */
    public static function clearHydrationContext(): void
    {
        self::$hydrationContext = [];
    }

    /**
     * Clear application context only.
     */
    public static function clearApplicationContext(): void
    {
        self::$applicationContext = [];
    }

    /**
     * Clear all context (both application and hydration).
     */
    public static function clear(): void
    {
        self::$applicationContext = [];
        self::$hydrationContext = [];
    }

    /**
     * Get all context values (merged, hydration context takes precedence).
     * 
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return array_merge(self::$applicationContext, self::$hydrationContext);
    }

    /**
     * Get application context values only.
     * 
     * @return array<string, mixed>
     */
    public static function getApplicationContext(): array
    {
        return self::$applicationContext;
    }

    /**
     * Get hydration context values only.
     * 
     * @return array<string, mixed>
     */
    public static function getHydrationContext(): array
    {
        return self::$hydrationContext;
    }
}
