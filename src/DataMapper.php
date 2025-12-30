<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper;

use Kassko\DataMapper\DataCollector\DataLineageCollector;
use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class DataMapper
{
    private ServiceResolver $serviceResolver;
    private ?CacheInterface $cache;
    private ?LoggerInterface $logger;
    private DataLineageCollector $lineageCollector;
    private Loader $loader;
    private ?Hydrator $hydrator = null;

    /**
     * @param ServiceResolver $serviceResolver
     * @param CacheInterface|null $cache PSR-16 cache interface
     * @param LoggerInterface|null $logger PSR-3 logger interface
     * @param array<string, callable> $customHydrators Custom hydrators (key => callable)
     */
    public function __construct(
        ServiceResolver $serviceResolver,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null,
        array $customHydrators = []
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->serviceResolver = $serviceResolver;
        $this->lineageCollector = new DataLineageCollector();
        
        // Register Loader in the registry
        $this->loader = new Loader($this->serviceResolver, $this->logger, $customHydrators, $this->lineageCollector);
        LoaderRegistry::set($this->loader);
        
        // Set logger for context registry
        if ($this->logger !== null) {
            ContextRegistry::setLogger($this->logger);
        }
    }

    public function getServiceResolver(): ServiceResolver
    {
        return $this->serviceResolver;
    }

    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * Get the data lineage collector.
     * 
     * Use this to enable/disable data collection and retrieve lineage information.
     */
    public function getLineageCollector(): DataLineageCollector
    {
        return $this->lineageCollector;
    }

    /**
     * Enable data lineage collection.
     * 
     * When enabled, the DataMapper will record all data flow events during hydration.
     * This is useful for debugging and can be used by tools like Symfony Profiler.
     * 
     * @return self For method chaining
     */
    public function enableLineageCollection(): self
    {
        $this->lineageCollector->enable();
        return $this;
    }

    /**
     * Disable data lineage collection.
     * 
     * @return self For method chaining
     */
    public function disableLineageCollection(): self
    {
        $this->lineageCollector->disable();
        return $this;
    }

    /**
     * Add a value to the application context.
     * 
     * Application context values are available during hydration via context() expressions.
     * They persist across hydration operations and can be overridden by #[Context] attributes.
     * 
     * @param string $key Context key
     * @param mixed $value Context value (can be any type)
     * @return self For method chaining
     */
    public function addToContext(string $key, mixed $value): self
    {
        ContextRegistry::addToApplicationContext($key, $value);
        return $this;
    }

    /**
     * Add multiple values to the application context at once.
     * 
     * @param array<string, mixed> $values Key-value pairs
     * @return self For method chaining
     */
    public function addManyToContext(array $values): self
    {
        ContextRegistry::addManyToApplicationContext($values);
        return $this;
    }

    /**
     * Clear the application context.
     * 
     * @return self For method chaining
     */
    public function clearContext(): self
    {
        ContextRegistry::clearApplicationContext();
        return $this;
    }

    /**
     * Get a context value.
     * 
     * @param string $key Context key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getContext(string $key, mixed $default = null): mixed
    {
        return ContextRegistry::get($key, $default, false);
    }

    /**
     * Check if a context key exists.
     * 
     * @param string $key Context key
     * @return bool
     */
    public function hasContext(string $key): bool
    {
        return ContextRegistry::has($key);
    }

    /**
     * Get the hydrator for hydrating objects from raw data.
     *
     * The Hydrator provides a simple interface for creating and hydrating objects:
     *
     * ```php
     * $hydrator = $dataMapper->getHydrator();
     * $person = $hydrator->hydrate(Person::class, ['first_name' => 'John']);
     * ```
     *
     * @return Hydrator
     */
    public function getHydrator(): Hydrator
    {
        if ($this->hydrator === null) {
            $this->hydrator = new Hydrator($this->loader);
        }
        
        return $this->hydrator;
    }
}
