<?php

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\Registry;

/**
 * Add Lazy loading feature to an entity.
 *
 * @author kko
 */
trait LazyLoadableTrait
{
    private static $loaded;

    private function loadProperty($propertyName)
    {
        $objectHash = spl_object_hash($this);

        if (! isset(self::$loaded[$objectHash][$propertyName])) {

            $lazyLoader = $this->getLazyLoader();
            $lazyLoader->loadProperty($this, $propertyName);

            if (! isset(self::$loaded[$objectHash])) {
                self::$loaded[$objectHash] = [];
            }
            self::$loaded[$objectHash][$propertyName] = true;

            //Mark properties loaded when $propertyName is loaded.
            foreach ($lazyLoader->getPropertiesLoadedTogether($propertyName) as $otherLoadedPropertyName) {
                self::$loaded[$objectHash][$otherLoadedPropertyName] = true;
            }
        }
    }

    private function getLazyLoader()
    {
        static $lazyLoader;
        return $lazyLoader = $lazyLoader ?: Registry::getInstance()[Registry::KEY_LAZY_LOADER_FACTORY]->getInstance(get_called_class());
    }
}
