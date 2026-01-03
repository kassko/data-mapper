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

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use PHPUnit\Framework\TestCase;

class PropertyNamePrecedenceTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    /**
     * Test that Property attribute fields take precedence over PropertyConfig fields during merge.
     * 
     * This is a unit test for the mergePropertyConfig logic.
     * When both Property and PropertyConfig have a value for the same field,
     * Property's value should take precedence.
     */
    public function testPropertyTakesPrecedenceOverPropertyConfigInMerge(): void
    {
        // Create Property with key set
        $property = new Property(
            key: 'property_key',
            class: null,
            expand: 'property_expand',
            noExpand: null,
            mapping: null,
        );
        
        // Create PropertyConfig with different values
        $config = new PropertyConfig(
            id: 'testConfig',
            class: 'SomeClass',
            key: 'config_key',
            expand: 'config_expand',
            noExpand: 'config_noExpand',
            mapping: null,
        );
        
        // Access the private mergePropertyConfig method via reflection
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $loader = LoaderRegistry::get();
        $method = new \ReflectionMethod($loader, 'mergePropertyConfig');
        
        $merged = $method->invoke($loader, $property, $config);
        
        // Property.key takes precedence
        $this->assertEquals('property_key', $merged->key);
        
        // Property.expand takes precedence
        $this->assertEquals('property_expand', $merged->expand);
        
        // Property.class is null, so PropertyConfig.class is used
        $this->assertEquals('SomeClass', $merged->class);
        
        // Property.noExpand is null, so PropertyConfig.noExpand is used  
        $this->assertEquals('config_noExpand', $merged->noExpand);
    }

    /**
     * Test that PropertyConfig values are used when Property values are null.
     */
    public function testPropertyConfigUsedWhenPropertyValuesAreNull(): void
    {
        // Create Property with only config reference (most fields null)
        $property = new Property(
            key: null,
            class: null,
            expand: null,
            noExpand: null,
            mapping: null,
        );
        
        // Create PropertyConfig with all values
        $config = new PropertyConfig(
            id: 'testConfig',
            class: 'ConfigClass',
            key: 'config_key',
            expand: 'config_expand',
            noExpand: 'config_noExpand',
            mapping: ['a' => 'b'],
        );
        
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        $loader = LoaderRegistry::get();
        $method = new \ReflectionMethod($loader, 'mergePropertyConfig');
        
        $merged = $method->invoke($loader, $property, $config);
        
        // All PropertyConfig values should be used
        $this->assertEquals('config_key', $merged->key);
        $this->assertEquals('ConfigClass', $merged->class);
        $this->assertEquals('config_expand', $merged->expand);
        $this->assertEquals('config_noExpand', $merged->noExpand);
        $this->assertEquals(['a' => 'b'], $merged->mapping);
    }
}
