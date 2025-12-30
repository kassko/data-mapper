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

use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\LoaderRegistry;

/**
 * Hydrator provides a simple interface for hydrating objects from raw data.
 *
 * This class wraps the internal Loader functionality and exposes a clean API
 * for instantiating and hydrating objects from associative arrays.
 *
 * Usage:
 * ```php
 * $hydrator = $dataMapper->getHydrator();
 * $person = $hydrator->hydrate(Person::class, ['first_name' => 'John', 'last_name' => 'Doe']);
 * ```
 */
final class Hydrator
{
    private Loader $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Hydrate an object from raw data.
     *
     * Creates a new instance of the specified class and hydrates its properties
     * from the provided raw data array.
     *
     * @template T of object
     * @param class-string<T> $className The fully qualified class name to instantiate
     * @param array<string, mixed> $rawData The raw data to hydrate the object with
     * @return T The hydrated object instance
     *
     * @throws \InvalidArgumentException If the class cannot be instantiated
     * @throws \ReflectionException If reflection fails
     */
    public function hydrate(string $className, array $rawData): object
    {
        // Instantiate the object with Param attribute support
        $object = $this->loader->instantiateWithParams($className);
        
        // Execute instantiating hooks
        $this->executeInstantiatingHooks($object, $rawData);
        
        // Hydrate the object with raw data
        $this->hydrateObject($object, $rawData);
        
        return $object;
    }

    /**
     * Hydrate an existing object from raw data.
     *
     * Hydrates the properties of an existing object instance from the provided raw data array.
     *
     * @param object $object The object instance to hydrate
     * @param array<string, mixed> $rawData The raw data to hydrate the object with
     * @return object The same object instance, now hydrated
     */
    public function hydrateExisting(object $object, array $rawData): object
    {
        // Execute instantiating hooks (still called for consistency)
        $this->executeInstantiatingHooks($object, $rawData);
        
        // Hydrate the object with raw data
        $this->hydrateObject($object, $rawData);
        
        return $object;
    }

    /**
     * Execute instantiating hooks on the object.
     *
     * @param object $object The object to execute hooks on
     * @param array<string, mixed> $rawData The raw data
     */
    private function executeInstantiatingHooks(object $object, array $rawData): void
    {
        // Use reflection to call the private method on Loader
        $reflectionMethod = new \ReflectionMethod($this->loader, 'executeInstantiatingHooks');
        $reflectionMethod->invoke($this->loader, $object, $rawData);
    }

    /**
     * Hydrate the object with raw data.
     *
     * @param object $object The object to hydrate
     * @param array<string, mixed> $rawData The raw data
     */
    private function hydrateObject(object $object, array $rawData): void
    {
        // Use reflection to call the private method on Loader
        $reflectionMethod = new \ReflectionMethod($this->loader, 'hydrateObject');
        $reflectionMethod->invoke($this->loader, $object, $rawData, null, 0);
    }
}
