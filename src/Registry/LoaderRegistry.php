<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Registry;

use Kassko\DataMapper\Loader\LoaderInterface;

final class LoaderRegistry
{
    private static ?LoaderInterface $loader = null;

    /**
     * Set the global Loader instance.
     * Called by DataMapperBuilder::build()
     */
    public static function set(LoaderInterface $loader): void
    {
        self::$loader = $loader;
    }

    /**
     * Get the global Loader instance.
     * Called by LoadableTrait::loadProperty()
     */
    public static function get(): ?LoaderInterface
    {
        return self::$loader;
    }

    /**
     * Check if a Loader has been registered.
     */
    public static function has(): bool
    {
        return self::$loader !== null;
    }

    /**
     * Clear the registry (useful for tests).
     */
    public static function clear(): void
    {
        self::$loader = null;
    }
}
