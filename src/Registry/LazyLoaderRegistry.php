<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Registry;

use Kassko\DataMapper\LazyLoader\LazyLoaderInterface;

final class LazyLoaderRegistry
{
    private static ?LazyLoaderInterface $lazyLoader = null;

    /**
     * Set the global LazyLoader instance.
     * Called by DataMapperBuilder::build()
     */
    public static function set(LazyLoaderInterface $lazyLoader): void
    {
        self::$lazyLoader = $lazyLoader;
    }

    /**
     * Get the global LazyLoader instance.
     * Called by LoadableTrait::loadProperty()
     */
    public static function get(): ?LazyLoaderInterface
    {
        return self::$lazyLoader;
    }

    /**
     * Check if a LazyLoader has been registered.
     */
    public static function has(): bool
    {
        return self::$lazyLoader !== null;
    }

    /**
     * Clear the registry (useful for tests).
     */
    public static function clear(): void
    {
        self::$lazyLoader = null;
    }
}
