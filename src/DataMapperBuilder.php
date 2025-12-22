<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

final class DataMapperBuilder
{
    private ?ContainerInterface $container = null;
    private ?CacheInterface $cache = null;
    
    /** @var ServiceLocatorInterface[] */
    private array $locators = [];
    
    /** @var array<array{object, string}> */
    private array $factoryServices = [];
    
    /** @var array<array{string|object, string}> */
    private array $staticFactories = [];
    
    /** @var callable[] */
    private array $callables = [];

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    public function addLocator(ServiceLocatorInterface $locator): self
    {
        $this->locators[] = $locator;
        return $this;
    }

    /**
     * Add a factory service that can create instances.
     * The factory method receives the service key as argument.
     *
     * @param object $factoryService The factory service instance
     * @param string $method The method to call on the factory service
     */
    public function addFactoryService(object $factoryService, string $method): self
    {
        $this->factoryServices[] = [$factoryService, $method];
        return $this;
    }

    /**
     * Add a static factory (class with static method) that can create instances.
     * The factory method receives the service key as argument.
     *
     * @param string|object $factoryClass The factory class name or instance
     * @param string $method The static method to call
     */
    public function addStaticFactory(string|object $factoryClass, string $method): self
    {
        $this->staticFactories[] = [$factoryClass, $method];
        return $this;
    }

    /**
     * Add a callable that can create instances.
     * The callable receives the service key as argument.
     * 
     * Supports all callable types:
     * - ['MyClass', 'myMethod']
     * - [$this, 'myMethod']
     * - 'MyClass::myMethod'
     * - Closure::fromCallable(...)
     * - fn($key) => ...
     *
     * @param callable $callable The callable factory
     */
    public function addCallable(callable $callable): self
    {
        $this->callables[] = $callable;
        return $this;
    }

    public function build(): DataMapper
    {
        $serviceResolver = new ServiceResolver(
            $this->container,
            $this->locators,
            $this->factoryServices,
            $this->staticFactories,
            $this->callables
        );
        $loader = new Loader($serviceResolver);
        
        // Register the Loader globally
        LoaderRegistry::set($loader);
        
        return new DataMapper($serviceResolver, $this->cache);
    }
}
