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
        $this->assertNull($ref->candidates);
        $this->assertNull($ref->defaultCandidate);
    }
    
    public function testIdWithFallbacksIsValid(): void
    {
        $ref = new DataSourceRef(
            id: 'sourceA',
            fallbacks: ['sourceB', 'sourceC'],
            exceptionOnNoValidFallback: 'SomeException'
        );
        $this->assertEquals('sourceA', $ref->id);
        $this->assertEquals(['sourceB', 'sourceC'], $ref->fallbacks);
        $this->assertEquals('SomeException', $ref->exceptionOnNoValidFallback);
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
        $this->expectExceptionMessage('DataSourceRef: exceptionOnNoValidFallback requires fallbacks to be set');
        
        new DataSourceRef(
            id: 'sourceA',
            exceptionOnNoValidFallback: 'SomeException'
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

    public function testCandidatesWithDefaultCandidateIsValid(): void
    {
        $ref = new DataSourceRef(
            candidates: [
                ['id' => 'sourceA', 'rule' => 'expr(true)'],
                ['id' => 'sourceB', 'rule' => 'expr(false)', 'priority' => 15],
            ],
            defaultCandidate: ['id' => 'sourceC']
        );
        $this->assertCount(2, $ref->candidates);
        $this->assertEquals('sourceC', $ref->defaultCandidate['id']);
        $this->assertNull($ref->id);
        $this->assertNull($ref->providers);
        $this->assertNull($ref->fallbacks);
    }

    public function testCandidatesWithoutDefaultCandidateThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: candidates and defaultCandidate must both be present or both absent');

        new DataSourceRef(candidates: [
            ['id' => 'sourceA', 'rule' => 'expr(true)'],
        ]);
    }

    public function testDefaultCandidateWithoutCandidatesThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: candidates and defaultCandidate must both be present or both absent');

        new DataSourceRef(
            candidates: null,
            defaultCandidate: ['id' => 'sourceB']
        );
    }

    public function testCandidatesAndIdAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, providers, and candidates are mutually exclusive');

        new DataSourceRef(
            id: 'sourceA',
            candidates: [['id' => 'sourceB', 'rule' => 'expr(true)']],
            defaultCandidate: ['id' => 'sourceC']
        );
    }

    public function testCandidatesAndProvidersAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, providers, and candidates are mutually exclusive');

        new DataSourceRef(
            providers: ['providerA'],
            candidates: [['id' => 'sourceB', 'rule' => 'expr(true)']],
            defaultCandidate: ['id' => 'sourceC']
        );
    }

    public function testCandidatesMustHaveId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('candidate at index 0 must have an "id" key');

        new DataSourceRef(
            candidates: [
                ['rule' => 'expr(true)'],
            ],
            defaultCandidate: ['id' => 'sourceB']
        );
    }

    public function testCandidatesMustHaveRule(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('candidate at index 0 must have a "rule" key');

        new DataSourceRef(
            candidates: [
                ['id' => 'sourceA'],
            ],
            defaultCandidate: ['id' => 'sourceB']
        );
    }

    public function testDefaultCandidateMustHaveId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: defaultCandidate must have an "id" key');

        new DataSourceRef(
            candidates: [
                ['id' => 'sourceA', 'rule' => 'expr(true)'],
            ],
            defaultCandidate: ['priority' => 10]
        );
    }

    public function testCandidatesWithPriorityIsValid(): void
    {
        $ref = new DataSourceRef(
            candidates: [
                ['id' => 'sourceA', 'rule' => 'expr(true)', 'priority' => 20],
            ],
            defaultCandidate: ['id' => 'sourceB'],
            priority: 5
        );
        $this->assertEquals(5, $ref->priority);
        $this->assertEquals(20, $ref->candidates[0]['priority']);
    }

    public function testCandidatesCannotUseFallbacks(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: fallbacks cannot be used with candidates');

        new DataSourceRef(
            candidates: [
                ['id' => 'sourceA', 'rule' => 'expr(true)'],
            ],
            defaultCandidate: ['id' => 'sourceB'],
            fallbacks: ['sourceC']
        );
    }

    public function testCandidatesCannotUseExceptionOnNoValidDataSource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: exceptionOnNoValidFallback cannot be used with candidates');

        new DataSourceRef(
            candidates: [
                ['id' => 'sourceA', 'rule' => 'expr(true)'],
            ],
            defaultCandidate: ['id' => 'sourceB'],
            exceptionOnNoValidFallback: 'SomeException'
        );
    }
}
