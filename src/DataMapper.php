<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\LazyLoader\LazyLoader;
use Kassko\DataMapper\LazyLoader\LazyLoaderInterface;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Psr\Container\ContainerInterface;

class DataMapper
{
    private LazyLoaderInterface $lazyLoader;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->lazyLoader = new LazyLoader($container);
    }

    /**
     * Prepare an object for lazy loading by injecting the loader
     *
     * @param object $object The object to prepare (must use LoadableTrait)
     */
    public function prepare(object $object): void
    {
        // Check if object uses LoadableTrait
        if (!method_exists($object, 'setDataMapperLoader')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Object of class %s must use %s to support lazy loading',
                    get_class($object),
                    LoadableTrait::class
                )
            );
        }
        
        $object->setDataMapperLoader($this->lazyLoader);
    }
}
