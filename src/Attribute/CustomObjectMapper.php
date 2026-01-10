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

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * CustomObjectMapper allows defining a custom object mapper for a property.
 * 
 * This is used when you need custom logic to map a DTO source object to a domain object.
 * The mapper receives the source object and returns the mapped object.
 *
 * Usage:
 * ```php
 * class Garage
 * {
 *     #[CustomObjectMapper(
 *         key: 'car_object_mapper',
 *         inputClass: CarInfoDto::class,
 *         outputClass: Car::class
 *     )]
 *     private ?Car $car = null;
 * }
 * ```
 *
 * Register the mapper:
 * ```php
 * $builder->addCustomObjectMapper('car_object_mapper', function(CarInfoDto $dto): Car {
 *     return new Car($dto->brand, $dto->model);
 * });
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class CustomObjectMapper
{
    /**
     * @param string $key Key to identify the custom object mapper
     * @param string|null $inputClass Optional: expected input class for type checking
     * @param string|null $outputClass Optional: expected output class for type checking
     * @param bool $nullableInput Whether the input can be null (requires inputClass to be set)
     * @param bool $nullableOutput Whether the output can be null (requires outputClass to be set)
     * @param bool $cascade Whether this attribute cascades to child classes
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $inputClass = null,
        public readonly ?string $outputClass = null,
        public readonly bool $nullableInput = false,
        public readonly bool $nullableOutput = false,
        public readonly bool $cascade = true,
        public readonly bool $enabled = true,
    ) {
        // Validation: nullableInput requires inputClass
        if ($this->nullableInput && $this->inputClass === null) {
            throw new \InvalidArgumentException(
                'CustomObjectMapper: nullableInput can only be set when inputClass is also specified'
            );
        }
        
        // Validation: nullableOutput requires outputClass
        if ($this->nullableOutput && $this->outputClass === null) {
            throw new \InvalidArgumentException(
                'CustomObjectMapper: nullableOutput can only be set when outputClass is also specified'
            );
        }
    }
}
