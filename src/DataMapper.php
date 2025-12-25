<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\SimpleCache\CacheInterface;

final class DataMapper
{
    private ServiceResolver $serviceResolver;
    private ?CacheInterface $cache;

    /**
     * @param ServiceResolver $serviceResolver
     * @param CacheInterface|null $cache PSR-16 cache interface
     */
    public function __construct(
        ServiceResolver $serviceResolver,
        ?CacheInterface $cache = null
    ) {
        $this->cache = $cache;
        $this->serviceResolver = $serviceResolver;
        
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
