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
use Kassko\DataMapper\Registry\LoaderRegistry;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

final class DataMapperBuilder
{
    private ?ContainerInterface $container = null;
    private ?CacheInterface $cache = null;
    private ?LoggerInterface $logger = null;
    
    /** @var ServiceLocatorInterface[] */
    private array $locators = [];
    
    /** @var array<array{object, string}> */
    private array $factoryServices = [];
    
    /** @var array<array{string|object, string}> */
    private array $staticFactories = [];
    
    /** @var callable[] */
    private array $callables = [];
    
    /** @var array<string, callable> */
    private array $customHydrators = [];

    /** @var array<string, callable> */
    private array $customObjectMappers = [];

    /** @var array<string, SensitiveLevel> Global sensitive keys configuration */
    private array $sensitiveKeys = [];

    /** @var SensitiveLevel Default sensitive level for all properties */
    private SensitiveLevel $defaultSensitiveLevel = SensitiveLevel::SHOW;

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

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function addServiceLocator(ServiceLocatorInterface $locator): self
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

    /**
     * Add a custom hydrator
     *
     * @param string $key The identifier key for the hydrator
     * @param callable $callable The hydrator callable (receives array $data, returns object|null)
     * @return self
     */
    public function addCustomHydrator(string $key, callable $callable): self
    {
        $this->customHydrators[$key] = $callable;
        return $this;
    }

    /**
     * Add a custom object mapper
     *
     * @param string $key The identifier key for the object mapper
     * @param callable $callable The mapper callable (receives object $sourceObject, returns object|null)
     * @return self
     */
    public function addCustomObjectMapper(string $key, callable $callable): self
    {
        $this->customObjectMappers[$key] = $callable;
        return $this;
    }

    /**
     * Get the configured custom object mappers.
     *
     * @return array<string, callable>
     */
    public function getCustomObjectMappers(): array
    {
        return $this->customObjectMappers;
    }

    /**
     * Set the default sensitive level for all properties.
     * This is used when no specific level is set for a property.
     *
     * @param SensitiveLevel $level The default sensitive level
     * @return self
     */
    public function setDefaultSensitiveLevel(SensitiveLevel $level): self
    {
        $this->defaultSensitiveLevel = $level;
        return $this;
    }

    /**
     * Set all sensitive keys at once.
     * Replaces any existing sensitive keys configuration.
     *
     * @param array<string, SensitiveLevel> $sensitiveKeys Map of property names to their sensitive levels
     * @return self
     */
    public function setSensitiveKeys(array $sensitiveKeys): self
    {
        $this->sensitiveKeys = $sensitiveKeys;
        return $this;
    }

    /**
     * Add a sensitive key with its level.
     *
     * @param string $key The property name (can be a pattern like 'password', '*password*', 'user.ssn')
     * @param SensitiveLevel $level The sensitive level for this key
     * @return self
     */
    public function addSensitiveKey(string $key, SensitiveLevel $level): self
    {
        $this->sensitiveKeys[$key] = $level;
        return $this;
    }

    /**
     * Remove one or more sensitive keys.
     *
     * @param string ...$keys The property names to remove from sensitive configuration
     * @return self
     */
    public function removeSensitiveKeys(string ...$keys): self
    {
        foreach ($keys as $key) {
            unset($this->sensitiveKeys[$key]);
        }
        return $this;
    }

    /**
     * Get the configured sensitive keys.
     *
     * @return array<string, SensitiveLevel>
     */
    public function getSensitiveKeys(): array
    {
        return $this->sensitiveKeys;
    }

    /**
     * Get the default sensitive level.
     *
     * @return SensitiveLevel
     */
    public function getDefaultSensitiveLevel(): SensitiveLevel
    {
        return $this->defaultSensitiveLevel;
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
        
        // Create the DataMapper which will create and register the Loader
        return new DataMapper(
            $serviceResolver,
            $this->cache,
            $this->logger,
            $this->customHydrators,
            $this->customObjectMappers,
            $this->sensitiveKeys,
            $this->defaultSensitiveLevel
        );
    }
}
