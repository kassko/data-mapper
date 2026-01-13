<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Tests\Integration\CrossCuttingRules\DataObjectInstantiation;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\DataObjectInstantiation\PersonWithAllOptionalParams;
use Kassko\Sample\DataObjectInstantiation\PersonWithMixedParams;
use Kassko\Sample\DataObjectInstantiation\PersonWithOnlyParamAttributes;
use Kassko\Sample\DataObjectInstantiation\PersonWithRequiredParamWithoutAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Tests for data object instantiation rules.
 * 
 * Rules:
 * - Constructor parameters must either be optional OR have a #[Param] attribute
 * - Optional parameters without #[Param] use their default value
 * - Property references (#id, property('id'), ##object) are forbidden in constructor
 */
class DataObjectInstantiationTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function setUp(): void
    {
        ContextRegistry::clear();
        LoaderRegistry::clear();
    }

    protected function tearDown(): void
    {
        ContextRegistry::clear();
        LoaderRegistry::clear();
    }

    // ========================================================================
    // Valid Instantiation Patterns
    // ========================================================================

    public function testInstantiationWithAllOptionalParameters(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $object = $loader->instantiateWithParams(PersonWithAllOptionalParams::class);
        
        // All parameters use default values
        $this->assertNull($object->firstName);
        $this->assertNull($object->lastName);
        $this->assertEquals(0, $object->age);
    }

    public function testInstantiationWithOnlyParamAttributes(): void
    {
        ContextRegistry::set('defaultFirstName', 'John');
        ContextRegistry::set('defaultLastName', 'Doe');
        
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $object = $loader->instantiateWithParams(PersonWithOnlyParamAttributes::class);
        
        // All parameters resolved from context
        $this->assertEquals('John', $object->firstName);
        $this->assertEquals('Doe', $object->lastName);
    }

    public function testInstantiationWithMixedOptionalAndParamParameters(): void
    {
        ContextRegistry::set('userId', 'user-123');
        
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $object = $loader->instantiateWithParams(PersonWithMixedParams::class);
        
        // Optional parameter uses default value
        $this->assertNull($object->socialSecurityNumber);
        // Param-annotated parameter resolved from context
        $this->assertEquals('user-123', $object->id);
        // Optional parameter with default value
        $this->assertTrue($object->active);
    }

    // ========================================================================
    // Invalid Instantiation Patterns (Should Throw Exceptions)
    // ========================================================================

    public function testInstantiationWithRequiredParamWithoutAttributeThrows(): void
    {
        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $loader = LoaderRegistry::get();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must either be optional or have a #[Param] attribute');
        
        $loader->instantiateWithParams(PersonWithRequiredParamWithoutAttribute::class);
    }
}
