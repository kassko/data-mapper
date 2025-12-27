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

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\DataSourceRef;
use PHPUnit\Framework\TestCase;

class DataSourceRefValidationTest extends TestCase
{
    public function testIdOnlyIsValid(): void
    {
        $ref = new DataSourceRef(id: 'sourceA');
        $this->assertEquals('sourceA', $ref->id);
        $this->assertNull($ref->fallbacks);
        $this->assertNull($ref->providers);
    }
    
    public function testIdWithFallbacksIsValid(): void
    {
        $ref = new DataSourceRef(
            id: 'sourceA',
            fallbacks: ['sourceB', 'sourceC'],
            exceptionOnNoValidDataSource: 'SomeException'
        );
        $this->assertEquals('sourceA', $ref->id);
        $this->assertEquals(['sourceB', 'sourceC'], $ref->fallbacks);
        $this->assertEquals('SomeException', $ref->exceptionOnNoValidDataSource);
        $this->assertNull($ref->providers);
    }
    
    public function testProvidersOnlyIsValid(): void
    {
        $ref = new DataSourceRef(providers: ['providerA', 'providerB']);
        $this->assertEquals(['providerA', 'providerB'], $ref->providers);
        $this->assertNull($ref->id);
        $this->assertNull($ref->fallbacks);
    }
    
    public function testNoParametersThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef requires either id or providers');
        
        new DataSourceRef();
    }
    
    public function testIdAndProvidersAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id and providers are mutually exclusive');
        
        new DataSourceRef(
            id: 'sourceA',
            providers: ['providerB']
        );
    }
    
    public function testFallbacksWithoutIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: fallbacks can only be used with id');
        
        new DataSourceRef(
            fallbacks: ['sourceA'],
            providers: ['providerB']
        );
    }
    
    public function testExceptionWithoutFallbacksThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: exceptionOnNoValidDataSource requires fallbacks to be set');
        
        new DataSourceRef(
            id: 'sourceA',
            exceptionOnNoValidDataSource: 'SomeException'
        );
    }
    
    public function testPriorityDefaultsToZero(): void
    {
        $ref = new DataSourceRef(id: 'sourceA');
        $this->assertEquals(0, $ref->priority);
    }
    
    public function testPriorityCanBeSet(): void
    {
        $ref = new DataSourceRef(id: 'sourceA', priority: 10);
        $this->assertEquals(10, $ref->priority);
    }
}
