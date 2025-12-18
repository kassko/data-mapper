<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\LazyLoader\LazyLoader;
use Kassko\DataMapper\LazyLoader\LazyLoaderInterface;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Psr\Container\ContainerInterface;

final class DataMapper
{
    private LazyLoaderInterface $lazyLoader;

    /**
     * @param ServiceResolver|ContainerInterface|null $serviceResolverOrContainer
     */
    public function __construct(ServiceResolver|ContainerInterface|null $serviceResolverOrContainer = null)
    {
        // Support backward compatibility: allow ContainerInterface or null
        if ($serviceResolverOrContainer instanceof ServiceResolver) {
            $serviceResolver = $serviceResolverOrContainer;
        } elseif ($serviceResolverOrContainer instanceof ContainerInterface || $serviceResolverOrContainer === null) {
            // Backward compatibility: create a ServiceResolver with just the container
            $serviceResolver = new ServiceResolver($serviceResolverOrContainer, []);
        } else {
            throw new \InvalidArgumentException(
                'Argument must be ServiceResolver, ContainerInterface, or null'
            );
        }
        
        $this->lazyLoader = new LazyLoader($serviceResolver);
    }

    public function getServiceResolver(): ServiceResolver
    {
        return $this->lazyLoader->getServiceResolver();
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

