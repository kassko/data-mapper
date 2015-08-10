<?php

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\Registry;

/**
 * Add loading feature to an object.
 *
 * @author kko
 */
trait LoadableTrait
{
    public $__isRegistered = false;

    private function load()
    {
        if (false === $lazyLoader = $this->__getLoader()) {
            return; 
        }
            
        $lazyLoader->load($this);
    }

    private function loadProperty($propertyName)
    {
        if (false === $lazyLoader = $this->__getLoader()) {
            return; 
        }
            
        $lazyLoader->loadProperty($this, $propertyName);
    }

    private function __getLoader()
    {
        $registry = Registry::getInstance();
        if (! isset($registry[Registry::KEY_LAZY_LOADER_FACTORY])) {
            return false;
        } 

        $loaderFactory = $registry[Registry::KEY_LAZY_LOADER_FACTORY];

        return $loaderFactory->getInstance(get_called_class());
    }
}
