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
        $this->expectExceptionMessage('DataSourceRef requires one of: id, providers, or candidates');
        
        new DataSourceRef();
    }
    
    public function testIdAndProvidersAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, providers, and candidates are mutually exclusive');
        
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

    public function testCandidatesOnlyIsValid(): void
    {
        $ref = new DataSourceRef(candidates: [
            ['id' => 'sourceA', 'discriminator' => 'expr(true)'],
            ['id' => 'sourceB', 'discriminator' => 'expr(false)', 'priority' => 15],
        ]);
        $this->assertCount(2, $ref->candidates);
        $this->assertNull($ref->id);
        $this->assertNull($ref->providers);
        $this->assertNull($ref->fallbacks);
    }

    public function testCandidatesAndIdAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, providers, and candidates are mutually exclusive');

        new DataSourceRef(
            id: 'sourceA',
            candidates: [['id' => 'sourceB', 'discriminator' => 'expr(true)']]
        );
    }

    public function testCandidatesAndProvidersAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, providers, and candidates are mutually exclusive');

        new DataSourceRef(
            providers: ['providerA'],
            candidates: [['id' => 'sourceB', 'discriminator' => 'expr(true)']]
        );
    }

    public function testCandidatesMustHaveId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('candidate at index 0 must have an "id" key');

        new DataSourceRef(candidates: [
            ['discriminator' => 'expr(true)'],
        ]);
    }

    public function testCandidatesMustHaveDiscriminator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('candidate at index 0 must have a "discriminator" key');

        new DataSourceRef(candidates: [
            ['id' => 'sourceA'],
        ]);
    }

    public function testCandidatesWithPriorityIsValid(): void
    {
        $ref = new DataSourceRef(
            candidates: [
                ['id' => 'sourceA', 'discriminator' => 'expr(true)', 'priority' => 20],
            ],
            priority: 5
        );
        $this->assertEquals(5, $ref->priority);
        $this->assertEquals(20, $ref->candidates[0]['priority']);
    }
}
