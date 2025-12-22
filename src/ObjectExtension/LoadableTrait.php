<?php

declare(strict_types=1);

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\LoaderRegistry;

trait LoadableTrait
{
    private array $lockedProperties = [];

    /**
     * Load a property on-demand using the global Loader.
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
     * Load all eager properties. Call this after instantiation if needed.
     */
    public function loadEagerProperties(): void
    {
        $loader = LoaderRegistry::get();
        
        if ($loader === null) {
            return;
        }
        
        $loader->loadEagerProperties($this);
    }

    /**
     * Lock a property to prevent lazy/eager loading from modifying its value.
     */
    protected function lockProperty(string $propertyName): void
    {
        $this->lockedProperties[$propertyName] = true;
    }

    /**
     * Unlock a property to allow lazy/eager loading to modify its value.
     */
    protected function unlockProperty(string $propertyName): void
    {
        unset($this->lockedProperties[$propertyName]);
    }

    /**
     * Check if a property is locked.
     */
    public function isPropertyLocked(string $propertyName): bool
    {
        return $this->lockedProperties[$propertyName] ?? false;
    }
}
