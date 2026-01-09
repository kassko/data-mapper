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

/**
 * ObjectMapper provides a simple interface for mapping objects from DTO sources.
 *
 * This class wraps the internal Loader functionality and exposes a clean API
 * for mapping objects from DTOs (Data Transfer Objects) or other source objects.
 *
 * Usage:
 * ```php
 * $objectMapper = $dataMapper->getObjectMapper();
 * 
 * // Map a DTO to a domain object
 * $person = $objectMapper->map(Person::class, $personDto);
 * 
 * // Map to an existing object
 * $employee = $objectMapper->mapToExisting($existingEmployee, $personDto);
 * ```
 *
 * ObjectMapper works in combination with Hydrator:
 * - Hydrator: Maps raw data (arrays) to objects
 * - ObjectMapper: Maps source objects (DTOs) to domain objects
 *
 * Both can be used together - a domain object may have properties that are:
 * - Hydrated from raw data arrays (via DataSource)
 * - Mapped from DTO sources (via object data sources)
 */
final class ObjectMapper
{
    private Loader $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Map a source object (DTO) to a new domain object.
     *
     * Creates a new instance of the specified class and maps its properties
     * from the provided source object.
     *
     * @template T of object
     * @param class-string<T> $className The fully qualified class name to instantiate
     * @param object $sourceObject The source object (DTO) to map from
     * @return T The mapped object instance
     *
     * @throws \InvalidArgumentException If the class cannot be instantiated
     * @throws \ReflectionException If reflection fails
     */
    public function map(string $className, object $sourceObject): object
    {
        // Instantiate the object with Param attribute support
        $object = $this->loader->instantiateWithParams($className);
        
        // Convert source object to data and execute mapping
        $data = $this->convertObjectToData($sourceObject);
        
        // Execute instantiating hooks
        $this->executeInstantiatingHooks($object, $data);
        
        // Map the object from source data
        $this->mapObject($object, $sourceObject, $data);
        
        return $object;
    }

    /**
     * Map a source object (DTO) to an existing domain object.
     *
     * Maps the properties of an existing object instance from the provided source object.
     *
     * @param object $object The object instance to map to
     * @param object $sourceObject The source object (DTO) to map from
     * @return object The same object instance, now mapped
     */
    public function mapToExisting(object $object, object $sourceObject): object
    {
        // Convert source object to data
        $data = $this->convertObjectToData($sourceObject);
        
        // Execute instantiating hooks
        $this->executeInstantiatingHooks($object, $data);
        
        // Map the object from source data
        $this->mapObject($object, $sourceObject, $data);
        
        return $object;
    }

    /**
     * Convert a source object to an associative array.
     *
     * This method extracts all accessible properties from the source object
     * and returns them as an associative array that can be used for mapping.
     *
     * @param object $sourceObject The source object
     * @return array<string, mixed> The extracted data
     */
    private function convertObjectToData(object $sourceObject): array
    {
        $reflectionMethod = new \ReflectionMethod($this->loader, 'convertSourceToData');
        return $reflectionMethod->invoke($this->loader, $sourceObject);
    }

    /**
     * Execute instantiating hooks on the object.
     *
     * @param object $object The object to execute hooks on
     * @param array<string, mixed> $data The data
     */
    private function executeInstantiatingHooks(object $object, array $data): void
    {
        $reflectionMethod = new \ReflectionMethod($this->loader, 'executeInstantiatingHooks');
        $reflectionMethod->invoke($this->loader, $object, $data);
    }

    /**
     * Map the object from source object and data.
     *
     * @param object $object The object to map
     * @param object $sourceObject The source object
     * @param array<string, mixed> $data The extracted data
     */
    private function mapObject(object $object, object $sourceObject, array $data): void
    {
        $reflectionMethod = new \ReflectionMethod($this->loader, 'mapObjectFromSource');
        $reflectionMethod->invoke($this->loader, $object, $sourceObject, $data, null, 0);
    }
}
