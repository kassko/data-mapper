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

namespace Kassko\DataMapper\Tests\Integration\Attributes\MappingStrategy;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Hydrator;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Exception\MappingStrategyException;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\MappingStrategy\CustomMappingService;
use Kassko\Sample\MappingStrategy\PersonFromCommonCasesMix;
use Kassko\Sample\MappingStrategy\PersonFromConstantCase;
use Kassko\Sample\MappingStrategy\PersonFromDashCase;
use Kassko\Sample\MappingStrategy\PersonFromPascalCase;
use Kassko\Sample\MappingStrategy\PersonFromUnderscoreCase;
use Kassko\Sample\MappingStrategy\PersonWithConflictingSourceField;
use Kassko\Sample\MappingStrategy\PersonWithCustomMapping;
use Kassko\Sample\MappingStrategy\PersonWithPropertyOverride;
use Kassko\Sample\MappingStrategy\PersonWithStringPreset;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for MappingStrategy attribute.
 * 
 * MappingStrategy allows mapping source fields with different naming conventions
 * (underscore_case, dash-case, PascalCase, CONSTANT_CASE, etc.) to camelCase properties.
 */
class MappingStrategyTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private DataMapper $dataMapper;
    private Hydrator $hydrator;

    protected function setUp(): void
    {
        $builder = new DataMapperBuilder();
        $this->dataMapper = $builder->build();
        $this->hydrator = $this->dataMapper->getHydrator();
    }

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    // ========================================================================
    // Preset Strategy Tests
    // ========================================================================

    /**
     * Test mapping from underscore_case source fields.
     */
    public function testFromUnderscoreCase(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'billing_address' => '123 Main St',
        ];

        $person = $this->hydrator->hydrate(PersonFromUnderscoreCase::class, $data);

        $this->assertEquals('John', $person->getFirstName());
        $this->assertEquals('Doe', $person->getLastName());
        $this->assertEquals('123 Main St', $person->getBillingAddress());
    }

    /**
     * Test mapping from dash-case source fields.
     */
    public function testFromDashCase(): void
    {
        $data = [
            'first-name' => 'Jane',
            'last-name' => 'Smith',
            'billing-address' => '456 Oak Ave',
        ];

        $person = $this->hydrator->hydrate(PersonFromDashCase::class, $data);

        $this->assertEquals('Jane', $person->getFirstName());
        $this->assertEquals('Smith', $person->getLastName());
        $this->assertEquals('456 Oak Ave', $person->getBillingAddress());
    }

    /**
     * Test mapping from PascalCase source fields.
     */
    public function testFromPascalCase(): void
    {
        $data = [
            'FirstName' => 'Bob',
            'LastName' => 'Wilson',
            'BillingAddress' => '789 Pine Rd',
        ];

        $person = $this->hydrator->hydrate(PersonFromPascalCase::class, $data);

        $this->assertEquals('Bob', $person->getFirstName());
        $this->assertEquals('Wilson', $person->getLastName());
        $this->assertEquals('789 Pine Rd', $person->getBillingAddress());
    }

    /**
     * Test mapping from CONSTANT_CASE source fields.
     */
    public function testFromConstantCase(): void
    {
        $data = [
            'FIRST_NAME' => 'Alice',
            'LAST_NAME' => 'Brown',
            'BILLING_ADDRESS' => '101 Maple Ln',
        ];

        $person = $this->hydrator->hydrate(PersonFromConstantCase::class, $data);

        $this->assertEquals('Alice', $person->getFirstName());
        $this->assertEquals('Brown', $person->getLastName());
        $this->assertEquals('101 Maple Ln', $person->getBillingAddress());
    }

    /**
     * Test default mapping behavior (camel + dash + underscore case).
     * Without explicit MappingStrategy, these 3 basic formats are supported.
     */
    public function testDefaultMappingBehavior(): void
    {
        $data = [
            'first_name' => 'Charlie',           // underscore_case
            'last-name' => 'Davis',              // dash-case
            'billingAddress' => '202 Elm St',    // camelCase
        ];

        $person = $this->hydrator->hydrate(PersonFromCommonCasesMix::class, $data);

        $this->assertEquals('Charlie', $person->getFirstName());
        $this->assertEquals('Davis', $person->getLastName());
        $this->assertEquals('202 Elm St', $person->getBillingAddress());
    }

    /**
     * Test string preset values (alternative to enum).
     */
    public function testStringPresetValue(): void
    {
        $data = [
            'first_name' => 'David',
            'last_name' => 'Evans',
        ];

        $person = $this->hydrator->hydrate(PersonWithStringPreset::class, $data);

        $this->assertEquals('David', $person->getFirstName());
        $this->assertEquals('Evans', $person->getLastName());
    }

    // ========================================================================
    // Property Override Tests
    // ========================================================================

    /**
     * Test property-level override of class-level mapping strategy.
     */
    public function testPropertyOverridesClassStrategy(): void
    {
        $data = [
            'first_name' => 'Emma',          // underscore_case (class strategy)
            'last-name' => 'Foster',         // dash-case (property override)
            'billing_address' => '303 Birch Blvd', // underscore_case (class strategy)
        ];

        $person = $this->hydrator->hydrate(PersonWithPropertyOverride::class, $data);

        $this->assertEquals('Emma', $person->getFirstName());
        $this->assertEquals('Foster', $person->getLastName());
        $this->assertEquals('303 Birch Blvd', $person->getBillingAddress());
    }

    // ========================================================================
    // Custom Callable Tests
    // ========================================================================

    /**
     * Test custom callable mapping strategy.
     */
    public function testCustomCallableMapping(): void
    {
        $customService = new CustomMappingService();
        $locator = new \Kassko\DataMapper\ArrayServiceLocator([
            CustomMappingService::class => $customService,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($locator);
        
        $dataMapper = $builder->build();
        $hydrator = $dataMapper->getHydrator();

        $data = [
            'custom_first_name' => 'Frank',
            'custom_last_name' => 'Garcia',
            'custom_billing_address' => '404 Cedar Ct',
        ];

        $person = $hydrator->hydrate(PersonWithCustomMapping::class, $data);

        $this->assertEquals('Frank', $person->getFirstName());
        $this->assertEquals('Garcia', $person->getLastName());
        $this->assertEquals('404 Cedar Ct', $person->getBillingAddress());
    }

    // ========================================================================
    // Validation Tests
    // ========================================================================

    /**
     * Test that sourceField and MappingStrategy on the same property throws exception.
     */
    public function testSourceFieldAndMappingStrategyAreMutuallyExclusive(): void
    {
        $data = [
            'explicit_first_name' => 'Test',
        ];

        $this->expectException(MappingStrategyException::class);
        $this->expectExceptionMessage('sourceField and MappingStrategy');

        $this->hydrator->hydrate(PersonWithConflictingSourceField::class, $data);
    }

    /**
     * Test that both preset and custom throws exception.
     */
    public function testPresetAndCustomAreMutuallyExclusive(): void
    {
        $this->expectException(MappingStrategyException::class);
        $this->expectExceptionMessage('mutually exclusive');

        // This should throw immediately when creating the attribute
        new \Kassko\DataMapper\Attribute\MappingStrategy(
            preset: 'from_underscore_case',
            custom: [CustomMappingService::class, 'mapPropertyToSource']
        );
    }

    /**
     * Test that neither preset nor custom throws exception.
     */
    public function testNeitherPresetNorCustomThrowsException(): void
    {
        $this->expectException(MappingStrategyException::class);
        $this->expectExceptionMessage('Either "preset" or "custom" must be defined');

        // This should throw immediately when creating the attribute
        new \Kassko\DataMapper\Attribute\MappingStrategy();
    }

    /**
     * Test that invalid preset throws exception.
     */
    public function testInvalidPresetThrowsException(): void
    {
        $this->expectException(MappingStrategyException::class);
        $this->expectExceptionMessage('Invalid preset');

        new \Kassko\DataMapper\Attribute\MappingStrategy(preset: 'invalid_preset');
    }

    /**
     * Test that disabled MappingStrategy is allowed with no preset/custom.
     */
    public function testDisabledMappingStrategyAllowsEmptyConfiguration(): void
    {
        // This should NOT throw because enabled=false
        $strategy = new \Kassko\DataMapper\Attribute\MappingStrategy(enabled: false);
        
        $this->assertFalse($strategy->enabled);
        $this->assertNull($strategy->preset);
        $this->assertNull($strategy->custom);
    }
}
