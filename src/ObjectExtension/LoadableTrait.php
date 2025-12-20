<?php

declare(strict_types=1);

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\LazyLoaderRegistry;

trait LoadableTrait
{
    private array $lockedProperties = [];

    /**
     * Load a property on-demand using the global LazyLoader.
     */
    protected function loadProperty(string $propertyName): void
    {
        $lazyLoader = LazyLoaderRegistry::get();
        
        if ($lazyLoader === null) {
            // No DataMapper configured - silently skip
            // This allows objects to work even without DataMapper
            return;
        }

        $lazyLoader->loadProperty($this, $propertyName);
    }

    /**
     * Load all eager properties. Call this after instantiation if needed.
     */
    public function loadEagerProperties(): void
    {
        $lazyLoader = LazyLoaderRegistry::get();
        
        if ($lazyLoader === null) {
            return;
        }
        
        $lazyLoader->loadEagerProperties($this);
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
