<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\LazyLoader\LazyLoader;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Psr\Container\ContainerInterface;

final class DataMapper
{
    private ServiceResolver $serviceResolver;

    /**
     * @param ServiceResolver|ContainerInterface|null $serviceResolverOrContainer
     */
    public function __construct(ServiceResolver|ContainerInterface|null $serviceResolverOrContainer = null)
    {
        // Support backward compatibility: allow ContainerInterface or null
        if ($serviceResolverOrContainer instanceof ServiceResolver) {
            $this->serviceResolver = $serviceResolverOrContainer;
        } elseif ($serviceResolverOrContainer instanceof ContainerInterface || $serviceResolverOrContainer === null) {
            // Backward compatibility: create a ServiceResolver with just the container
            $this->serviceResolver = new ServiceResolver($serviceResolverOrContainer, []);
        } else {
            throw new \InvalidArgumentException(
                'Argument must be ServiceResolver, ContainerInterface, or null'
            );
        }
        
        // Register LazyLoader in the registry for backward compatibility
        $lazyLoader = new LazyLoader($this->serviceResolver);
        LazyLoaderRegistry::set($lazyLoader);
    }

    public function getServiceResolver(): ServiceResolver
    {
        return $this->serviceResolver;
    }
}

