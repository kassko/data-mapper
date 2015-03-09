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
        $lazyLoader = $this->getLazyLoader();
        if (false !== $lazyLoader) {
            
            $objectHash = spl_object_hash($this);
            if (! isset(self::$loaded[$objectHash][$propertyName])) {
             
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
    }

    private function getLazyLoader()
    {
        static $lazyLoader;

        if (null === $lazyLoader) {

            $registry = Registry::getInstance();
            if (isset($registry[Registry::KEY_LAZY_LOADER_FACTORY])) {
                $lazyLoader = $registry[Registry::KEY_LAZY_LOADER_FACTORY]->getInstance(get_called_class());
            } else {
                $lazyLoader = false;
            }
        }

        return $lazyLoader; 
    }
}
