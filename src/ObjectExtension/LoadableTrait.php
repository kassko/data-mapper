<?php

declare(strict_types=1);

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\LazyLoaderRegistry;

trait LoadableTrait
{
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
    protected function loadEagerProperties(): void
    {
        $lazyLoader = LazyLoaderRegistry::get();
        
        if ($lazyLoader === null) {
            return;
        }
        
        $lazyLoader->loadEagerProperties($this);
    }
}
