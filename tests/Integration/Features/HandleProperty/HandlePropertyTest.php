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

namespace Kassko\DataMapper\Tests\Integration\Features\HandleProperty;

use Kassko\DataMapper\Hydrator;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\HandleProperty\HandleAllPropertiesEntity;
use Kassko\Sample\HandleProperty\SkipAllExceptMarkedEntity;
use Kassko\Sample\HandleProperty\ConditionalHandleEntity;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for HandleProperty and HandleAllProperties attributes.
 * 
 * Tests the "value" and "when" logic:
 * - value=true + when=true -> hydrate
 * - value=true + when=false -> skip (SAUF/EXCEPT)
 * - value=false + when=true -> skip
 * - value=false + when=false -> hydrate (SAUF/EXCEPT)
 */
class HandlePropertyTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private Hydrator $hydrator;

    protected function setUp(): void
    {
        $builder = new DataMapperBuilder();
        $dataMapper = $builder->build();
        $this->hydrator = $dataMapper->getHydrator();
    }

    protected function tearDown(): void
    {
        ContextRegistry::clear();
    }

    // ==================== HandleAllProperties(value: true) Tests ====================

    public function testHandleAllPropertiesTrueHydratesAll(): void
    {
        $data = ['name' => 'John', 'email' => 'john@example.com'];
        
        $entity = $this->hydrator->hydrate(HandleAllPropertiesEntity::class, $data);
        
        // Both should be hydrated
        $this->assertEquals('John', $entity->getName());
        $this->assertEquals('john@example.com', $entity->getEmail());
    }

    // ==================== HandleAllProperties(value: false) Tests ====================

    public function testHandleAllPropertiesFalseSkipsUnmarked(): void
    {
        $data = ['name' => 'John', 'email' => 'john@example.com', 'phone' => '123'];
        
        $entity = $this->hydrator->hydrate(SkipAllExceptMarkedEntity::class, $data);
        
        // name should be hydrated (has HandleProperty(value: true))
        $this->assertEquals('John', $entity->getName());
        
        // email and phone should NOT be hydrated (no HandleProperty)
        $this->assertEquals('', $entity->getEmail());
        $this->assertEquals('', $entity->getPhone());
    }

    // ==================== Conditional HandleProperty Tests ====================

    public function testHandlePropertyValueTrueWhenTrue(): void
    {
        // Set context so 'include_email' exists
        ContextRegistry::set('include_email', true);
        
        $data = ['name' => 'John', 'email' => 'john@example.com', 'phone' => '123'];
        
        $entity = $this->hydrator->hydrate(ConditionalHandleEntity::class, $data);
        
        // name should be hydrated (no HandleProperty)
        $this->assertEquals('John', $entity->getName());
        
        // email: value=true, when=true -> hydrate
        $this->assertEquals('john@example.com', $entity->getEmail());
        
        // phone: value=false, when=false (hide_phone not set) -> hydrate (SAUF)
        $this->assertEquals('123', $entity->getPhone());
    }

    public function testHandlePropertyValueTrueWhenFalse(): void
    {
        // No context set - 'include_email' condition is FALSE
        
        $data = ['name' => 'John', 'email' => 'john@example.com', 'phone' => '123'];
        
        $entity = $this->hydrator->hydrate(ConditionalHandleEntity::class, $data);
        
        // name should be hydrated (no HandleProperty)
        $this->assertEquals('John', $entity->getName());
        
        // email: value=true, when=false -> skip (SAUF)
        $this->assertEquals('', $entity->getEmail());
        
        // phone: value=false, when=false (hide_phone not set) -> hydrate (SAUF)
        $this->assertEquals('123', $entity->getPhone());
    }

    public function testHandlePropertyValueFalseWhenTrue(): void
    {
        // Set context so 'hide_phone' exists
        ContextRegistry::set('hide_phone', true);
        
        $data = ['name' => 'John', 'email' => 'john@example.com', 'phone' => '123'];
        
        $entity = $this->hydrator->hydrate(ConditionalHandleEntity::class, $data);
        
        // name should be hydrated (no HandleProperty)
        $this->assertEquals('John', $entity->getName());
        
        // email: value=true, when=false (include_email not set) -> skip (SAUF)
        $this->assertEquals('', $entity->getEmail());
        
        // phone: value=false, when=true -> skip
        $this->assertEquals('', $entity->getPhone());
    }

    public function testHandlePropertyValueFalseWhenFalse(): void
    {
        // No context set - both conditions are FALSE
        
        $data = ['name' => 'John', 'email' => 'john@example.com', 'phone' => '123'];
        
        $entity = $this->hydrator->hydrate(ConditionalHandleEntity::class, $data);
        
        // name should be hydrated (no HandleProperty)
        $this->assertEquals('John', $entity->getName());
        
        // email: value=true, when=false -> skip (SAUF)
        $this->assertEquals('', $entity->getEmail());
        
        // phone: value=false, when=false -> hydrate (SAUF)
        $this->assertEquals('123', $entity->getPhone());
    }

    public function testHandlePropertyBothConditionsTrue(): void
    {
        // Set context so both conditions are TRUE
        ContextRegistry::set('include_email', true);
        ContextRegistry::set('hide_phone', true);
        
        $data = ['name' => 'John', 'email' => 'john@example.com', 'phone' => '123'];
        
        $entity = $this->hydrator->hydrate(ConditionalHandleEntity::class, $data);
        
        // name should be hydrated (no HandleProperty)
        $this->assertEquals('John', $entity->getName());
        
        // email: value=true, when=true -> hydrate
        $this->assertEquals('john@example.com', $entity->getEmail());
        
        // phone: value=false, when=true -> skip
        $this->assertEquals('', $entity->getPhone());
    }
}
