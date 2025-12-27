<?php

declare(strict_types=1);

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Registry\LockedPropertyRegistry;

/**
 * Trait for domain objects that need lazy loading capabilities.
 * 
 * Usage:
 * - Use this trait in your domain objects
 * - Call loadProperty() in your getters to trigger lazy loading
 * - Use lockProperty()/unlockProperty() to control when properties can be loaded
 * 
 * Note: Locked properties are tracked externally in LockedPropertyRegistry,
 * not in the object itself. This prevents the lock state from being serialized
 * with the object.
 */
trait LoadableTrait
{
    /**
     * Load a property on-demand using the global Loader.
     * 
     * Call this method in your getter before returning the property value.
     */
    protected function loadProperty(string $propertyName): void
    {
        $loader = LoaderRegistry::get();
        
        if ($loader === null) {
            // No DataMapper configured - silently skip
            // This allows objects to work even without DataMapper
            return;
        }

        $loader->loadProperty($this, $propertyName);
    }

    /**
     * Lock a property to prevent lazy/eager loading from modifying its value.
     */
    protected function lockProperty(string $propertyName): void
    {
        LockedPropertyRegistry::lock($this, $propertyName);
    }

    /**
     * Unlock a property to allow lazy/eager loading to modify its value.
     */
    protected function unlockProperty(string $propertyName): void
    {
        LockedPropertyRegistry::unlock($this, $propertyName);
    }

    /**
     * Check if a property is locked.
     * @internal Used by the Loader to check if a property should be loaded
     */
    public function isPropertyLocked(string $propertyName): bool
    {
        return LockedPropertyRegistry::isLocked($this, $propertyName);
    }
}
