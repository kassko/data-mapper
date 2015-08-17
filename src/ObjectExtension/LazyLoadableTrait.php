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
    public $__isRegistered = false;

    protected function loadProperty($propertyName)
    {
        if (false === $lazyLoader = $this->getLazyLoader()) {
            return; 
        }
            
        $lazyLoader->loadProperty($this, $propertyName);
    }

    private function getLazyLoader()
    {
        $registry = Registry::getInstance();
        if (isset($registry[Registry::KEY_LAZY_LOADER_FACTORY])) {
            $lazyLoader = $registry[Registry::KEY_LAZY_LOADER_FACTORY]->getInstance(get_called_class());
        } else {
            $lazyLoader = false;
        }

        return $lazyLoader; 
    }
}
