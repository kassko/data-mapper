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

namespace Kassko\DataMapper\Exception;

/**
 * Exception thrown when there's an issue with MappingStrategy configuration.
 */
class MappingStrategyException extends \InvalidArgumentException
{
    /**
     * Create exception for when both preset and custom are defined.
     */
    public static function presetAndCustomAreMutuallyExclusive(string $context): self
    {
        return new self(sprintf(
            'MappingStrategy: "preset" and "custom" are mutually exclusive. %s',
            $context
        ));
    }

    /**
     * Create exception for when neither preset nor custom is defined.
     */
    public static function presetOrCustomRequired(string $context): self
    {
        return new self(sprintf(
            'MappingStrategy: Either "preset" or "custom" must be defined (or use enabled=false to disable). %s',
            $context
        ));
    }

    /**
     * Create exception for when sourceField and MappingStrategy are both defined on a property.
     */
    public static function sourceFieldAndMappingStrategyAreMutuallyExclusive(string $propertyName, string $className): self
    {
        return new self(sprintf(
            'Property "%s" in class "%s" has both sourceField and MappingStrategy defined. These are mutually exclusive.',
            $propertyName,
            $className
        ));
    }

    /**
     * Create exception for an invalid preset value.
     */
    public static function invalidPreset(string $preset): self
    {
        return new self(sprintf(
            'MappingStrategy: Invalid preset "%s". Valid presets are: %s',
            $preset,
            implode(', ', array_column(\Kassko\DataMapper\Enum\MappingStrategyPreset::cases(), 'value'))
        ));
    }

    /**
     * Create exception for an invalid custom callable.
     */
    public static function invalidCustomCallable(string $context): self
    {
        return new self(sprintf(
            'MappingStrategy: Invalid custom callable. Must be a valid callable (class::method or Closure). %s',
            $context
        ));
    }
}
