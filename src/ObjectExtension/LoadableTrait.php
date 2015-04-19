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
        static $loaderFactory;

        if (false === $loaderFactory) {//If loader factory is not available.
            return false;
        } 

        if (null !== $loaderFactory) {//If loader factory is available.
            return $loaderFactory->getInstance(get_called_class());
        }

        //If loader factory availability is not evaluated yet.
        $registry = Registry::getInstance();
        if (isset($registry[Registry::KEY_LAZY_LOADER_FACTORY])) {
            $loaderFactory = $registry[Registry::KEY_LAZY_LOADER_FACTORY];
        } else {
            $loaderFactory = false;
        }

        return $loaderFactory->getInstance(get_called_class());
    }
}
