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
use Kassko\DataMapper\Enum\SensitiveLevel;
use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class DataMapper
{
    private ServiceResolver $serviceResolver;
    private ?CacheInterface $dataSourceCache;
    private ?CacheInterface $mappingCache;
    private bool $mappingStrategyEnabled;
    private ?LoggerInterface $logger;
    private DataLineageCollector $lineageCollector;
    private Loader $loader;
    private ?Hydrator $hydrator = null;
    private ?ObjectMapper $objectMapper = null;
    /** @var array<string, callable> */
    private array $customHydrators;
    /** @var array<string, callable> */
    private array $customObjectMappers;

    /**
     * @param ServiceResolver $serviceResolver
     * @param CacheInterface|null $dataSourceCache PSR-16 cache interface for data source results
     * @param CacheInterface|null $mappingCache PSR-16 cache interface for mapping strategy conversions
     * @param bool $mappingStrategyEnabled Whether MappingStrategy feature is enabled
     * @param LoggerInterface|null $logger PSR-3 logger interface
     * @param array<string, callable> $customHydrators Custom hydrators (key => callable)
     * @param array<string, callable> $customObjectMappers Custom object mappers (key => callable)
     * @param array<string, SensitiveLevel> $sensitiveKeys Global sensitive keys configuration
     * @param SensitiveLevel $defaultSensitiveLevel Default sensitive level for all properties
     */
    public function __construct(
        ServiceResolver $serviceResolver,
        ?CacheInterface $dataSourceCache = null,
        ?CacheInterface $mappingCache = null,
        bool $mappingStrategyEnabled = false,
        ?LoggerInterface $logger = null,
        array $customHydrators = [],
        array $customObjectMappers = [],
        array $sensitiveKeys = [],
        SensitiveLevel $defaultSensitiveLevel = SensitiveLevel::SHOW
    ) {
        $this->dataSourceCache = $dataSourceCache;
        $this->mappingCache = $mappingCache;
        $this->mappingStrategyEnabled = $mappingStrategyEnabled;
        $this->logger = $logger;
        $this->serviceResolver = $serviceResolver;
        $this->customHydrators = $customHydrators;
        $this->customObjectMappers = $customObjectMappers;
        $this->lineageCollector = new DataLineageCollector($sensitiveKeys, $defaultSensitiveLevel);
        
        // Register Loader in the registry
        $this->loader = new Loader(
            $this->serviceResolver,
            $this->logger,
            $customHydrators,
            $this->lineageCollector,
            null, // cascadeCollector
            $this->mappingCache,
            $this->mappingStrategyEnabled
        );
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

    /**
     * Ensure the loader is registered in the global LoaderRegistry.
     * 
     * This is useful when the registry has been cleared (e.g., between tests)
     * but the DataMapper instance is reused.
     * 
     * @return self For method chaining
     */
    public function ensureLoaderRegistered(): self
    {
        if (!LoaderRegistry::has()) {
            LoaderRegistry::set($this->loader);
        }
        return $this;
    }

    public function getDataSourceCache(): ?CacheInterface
    {
        return $this->dataSourceCache;
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

    /**
     * Get the object mapper for mapping objects from DTO sources.
     *
     * The ObjectMapper provides a simple interface for mapping DTO objects to domain objects:
     *
     * ```php
     * $objectMapper = $dataMapper->getObjectMapper();
     * $person = $objectMapper->map(Person::class, $personDto);
     * ```
     *
     * @return ObjectMapper
     */
    public function getObjectMapper(): ObjectMapper
    {
        if ($this->objectMapper === null) {
            $this->objectMapper = new ObjectMapper($this->loader);
        }
        
        return $this->objectMapper;
    }

    /**
     * Get a custom hydrator by key.
     *
     * Custom hydrators are registered via DataMapperBuilder::addCustomHydrator()
     * and can be used to provide custom hydration logic for specific properties.
     *
     * ```php
     * $customHydrator = $dataMapper->getCustomHydrator('my_hydrator');
     * if ($customHydrator !== null) {
     *     $value = $customHydrator($data, $context);
     * }
     * ```
     *
     * @param string $key The custom hydrator key
     * @return callable|null The hydrator callable or null if not found
     */
    public function getCustomHydrator(string $key): ?callable
    {
        return $this->customHydrators[$key] ?? null;
    }

    /**
     * Get a custom object mapper by key.
     *
     * Custom object mappers are registered via DataMapperBuilder::addCustomObjectMapper()
     * and can be used to provide custom mapping logic for specific properties when
     * mapping from DTO sources.
     *
     * ```php
     * $customMapper = $dataMapper->getCustomObjectMapper('address_mapper');
     * if ($customMapper !== null) {
     *     $address = $customMapper($addressDto, $context);
     * }
     * ```
     *
     * @param string $key The custom object mapper key
     * @return callable|null The mapper callable or null if not found
     */
    public function getCustomObjectMapper(string $key): ?callable
    {
        return $this->customObjectMappers[$key] ?? null;
    }
}
