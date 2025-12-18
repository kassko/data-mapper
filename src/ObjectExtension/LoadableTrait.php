<?php

declare(strict_types=1);

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\LazyLoader\LazyLoaderInterface;

trait LoadableTrait
{
    private ?LazyLoaderInterface $dataMapperLoader = null;

    /**
     * Set the lazy loader for this object
     *
     * @param LazyLoaderInterface $loader
     */
    public function setDataMapperLoader(LazyLoaderInterface $loader): void
    {
        $this->dataMapperLoader = $loader;
    }

    /**
     * Load a property lazily using the configured loader
     *
     * @param string $propertyName The name of the property to load
     */
    protected function loadProperty(string $propertyName): void
    {
        if ($this->dataMapperLoader !== null) {
            $this->dataMapperLoader->loadProperty($this, $propertyName);
        }
    }
}
