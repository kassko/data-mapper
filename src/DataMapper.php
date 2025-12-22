<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

final class DataMapper
{
    private ServiceResolver $serviceResolver;
    private ?CacheInterface $cache;

    /**
     * @param ServiceResolver|ContainerInterface|null $serviceResolverOrContainer
     * @param CacheInterface|null $cache PSR-16 cache interface
     */
    public function __construct(
        ServiceResolver|ContainerInterface|null $serviceResolverOrContainer = null,
        ?CacheInterface $cache = null
    ) {
        $this->cache = $cache;
        
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
        
        // Register Loader in the registry
        $loader = new Loader($this->serviceResolver);
        LoaderRegistry::set($loader);
    }

    public function getServiceResolver(): ServiceResolver
    {
        return $this->serviceResolver;
    }

    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }
}
