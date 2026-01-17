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

namespace Kassko\DataMapper\Tests\Integration\CrossCuttingRules\AttributeFieldsValidation;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for cross-cutting attribute field requirements.
 * 
 * These tests verify that all attributes in the DataMapper library follow
 * consistent patterns for common fields like 'enabled' and 'cascade'.
 * 
 * Rules verified:
 * - All attributes must have an 'enabled' field with default value true
 * - All attributes must have a 'cascade' field with default value true
 */
class AttributeFieldsValidationTest extends TestCase
{
    /**
     * List of attributes that are exempt from having 'enabled' field.
     * (e.g., simple marker attributes or attributes with different patterns)
     */
    private const ENABLED_EXEMPT_ATTRIBUTES = [
        // Context is repeatable and used differently
        'Context',
        // Param is used for constructor parameters
        'Param',
    ];

    /**
     * List of attributes that are exempt from having 'cascade' field.
     * (e.g., attributes that don't make sense to cascade)
     */
    private const CASCADE_EXEMPT_ATTRIBUTES = [
        // Context is specific to the property
        'Context',
        // Param is for constructor parameters, doesn't cascade
        'Param',
        // RejectAttributeCascading controls cascading itself, having a cascade field would be redundant
        'RejectAttributeCascading',
    ];

    /**
     * Get all attribute classes from src/Attribute directory.
     * 
     * @return array<class-string> List of fully qualified class names
     */
    private function getAllAttributeClasses(): array
    {
        $attributeDir = __DIR__ . '/../../../../src/Attribute';
        $classes = [];

        foreach (glob($attributeDir . '/*.php') as $file) {
            $className = basename($file, '.php');
            $fqcn = 'Kassko\\DataMapper\\Attribute\\' . $className;
            
            if (class_exists($fqcn)) {
                $reflection = new ReflectionClass($fqcn);
                // Only include actual attribute classes
                if (count($reflection->getAttributes(\Attribute::class)) > 0) {
                    $classes[$className] = $fqcn;
                }
            }
        }

        return $classes;
    }

    /**
     * Test that all attributes have an 'enabled' field with default value true.
     * 
     * @dataProvider attributeEnabledFieldProvider
     */
    public function testAllAttributesHaveEnabledFieldWithTrueDefault(string $shortName, string $fqcn): void
    {
        if (in_array($shortName, self::ENABLED_EXEMPT_ATTRIBUTES, true)) {
            $this->markTestSkipped("$shortName is exempt from enabled field requirement");
        }

        $reflection = new ReflectionClass($fqcn);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull(
            $constructor,
            "Attribute $shortName must have a constructor"
        );

        $parameters = $constructor->getParameters();
        $enabledParam = null;
        
        foreach ($parameters as $param) {
            if ($param->getName() === 'enabled') {
                $enabledParam = $param;
                break;
            }
        }

        $this->assertNotNull(
            $enabledParam,
            "Attribute $shortName must have an 'enabled' parameter"
        );

        $this->assertTrue(
            $enabledParam->isDefaultValueAvailable(),
            "Attribute $shortName 'enabled' parameter must have a default value"
        );

        $this->assertTrue(
            $enabledParam->getDefaultValue(),
            "Attribute $shortName 'enabled' parameter must default to true"
        );
    }

    /**
     * Test that all attributes have a 'cascade' field with default value true.
     * 
     * @dataProvider attributeCascadeFieldProvider
     */
    public function testAllAttributesHaveCascadeFieldWithTrueDefault(string $shortName, string $fqcn): void
    {
        if (in_array($shortName, self::CASCADE_EXEMPT_ATTRIBUTES, true)) {
            $this->markTestSkipped("$shortName is exempt from cascade field requirement");
        }

        $reflection = new ReflectionClass($fqcn);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull(
            $constructor,
            "Attribute $shortName must have a constructor"
        );

        $parameters = $constructor->getParameters();
        $cascadeParam = null;
        
        foreach ($parameters as $param) {
            if ($param->getName() === 'cascade') {
                $cascadeParam = $param;
                break;
            }
        }

        $this->assertNotNull(
            $cascadeParam,
            "Attribute $shortName must have a 'cascade' parameter"
        );

        $this->assertTrue(
            $cascadeParam->isDefaultValueAvailable(),
            "Attribute $shortName 'cascade' parameter must have a default value"
        );

        $this->assertTrue(
            $cascadeParam->getDefaultValue(),
            "Attribute $shortName 'cascade' parameter must default to true"
        );
    }

    /**
     * Data provider for enabled field tests.
     */
    public static function attributeEnabledFieldProvider(): array
    {
        $testCases = [];
        $attributeDir = __DIR__ . '/../../../../src/Attribute';

        foreach (glob($attributeDir . '/*.php') as $file) {
            $className = basename($file, '.php');
            $fqcn = 'Kassko\\DataMapper\\Attribute\\' . $className;
            
            if (class_exists($fqcn)) {
                $reflection = new ReflectionClass($fqcn);
                if (count($reflection->getAttributes(\Attribute::class)) > 0) {
                    $testCases[$className] = [$className, $fqcn];
                }
            }
        }

        return $testCases;
    }

    /**
     * Data provider for cascade field tests.
     */
    public static function attributeCascadeFieldProvider(): array
    {
        // Use same provider as enabled - they should all have both fields
        return self::attributeEnabledFieldProvider();
    }

    /**
     * Test to list all discovered attributes (informational).
     */
    public function testListAllAttributes(): void
    {
        $classes = $this->getAllAttributeClasses();
        
        $this->assertNotEmpty($classes, 'Should discover at least some attributes');
        
        // Just log for visibility
        $this->assertGreaterThan(
            10, 
            count($classes), 
            'Should have more than 10 attributes in the library'
        );
    }

    /**
     * Verify MappingStrategy specifically has both fields.
     */
    public function testMappingStrategyHasRequiredFields(): void
    {
        $reflection = new ReflectionClass(\Kassko\DataMapper\Attribute\MappingStrategy::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);

        $paramNames = array_map(
            fn($p) => $p->getName(),
            $constructor->getParameters()
        );

        $this->assertContains('enabled', $paramNames);
        $this->assertContains('cascade', $paramNames);
    }
}
