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

    protected function load()
    {
        if (false === $lazyLoader = $this->__getLoader()) {
            return;
        }

        $lazyLoader->load($this);
    }

    protected function loadProperty($propertyName)
    {
        if (false === $lazyLoader = $this->__getLoader()) {
            return;
        }

        $lazyLoader->loadProperty($this, $propertyName);
    }

    public function markPropertyLoaded($propertyName)
    {
        if (false === $lazyLoader = $this->__getLoader()) {
            return;
        }

        $lazyLoader->markPropertyLoaded($this, $propertyName);
    }

    public function isPropertyLoaded($propertyName)
    {
        if (false === $lazyLoader = $this->__getLoader()) {
            return;
        }

        $lazyLoader->isPropertyLoaded($this, $propertyName);
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
