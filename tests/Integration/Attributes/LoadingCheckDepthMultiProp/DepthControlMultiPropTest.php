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

namespace Kassko\DataMapper\Tests\Integration\Attributes\LoadingCheckDepthMultiProp;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\LoadingDepthMultiProp\CompanyWithMultiPropDepth0;
use Kassko\Sample\LoadingDepthMultiProp\CompanyWithMultiPropDepth1;
use Kassko\Sample\LoadingDepthMultiProp\Employee;
use Kassko\Sample\LoadingDepthMultiProp\Team;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Loading::depth attribute with MultiPropDataSource.
 * 
 * Verifies that depth control works not only with SinglePropDataSource
 * but also with MultiPropDataSource (via DataSourceRef).
 */
class DepthControlMultiPropTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    /**
     * Test that depth=0 with MultiPropDataSource hydrates only root scalars.
     * 
     * Team.name should be hydrated, but Team.leader (Employee) should NOT.
     */
    public function testMultiPropDepth0HydratesOnlyRootScalars(): void
    {
        new DataMapper(new ServiceResolver());
        
        $company = new CompanyWithMultiPropDepth0();
        
        // getTeam() triggers lazy loading via MultiPropDataSource
        $team = $company->getTeam();
        
        // Team should be hydrated with scalars
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Engineering Team', $team->getName());
        
        // Leader should NOT be hydrated (depth=0 prevents nested objects)
        $leader = $team->getLeader();
        $this->assertNull($leader, 'With depth=0, leader should not be hydrated into an Employee object');
        
        // Cross-hydration should work - company scalars should be set
        $this->assertEquals('Acme Corporation', $company->getName());
        $this->assertEquals('ACME-001', $company->getCode());
    }

    /**
     * Test that depth=1 with MultiPropDataSource hydrates root + direct children.
     * 
     * Team.name and Team.leader (Employee) should both be hydrated.
     */
    public function testMultiPropDepth1HydratesRootAndChildren(): void
    {
        new DataMapper(new ServiceResolver());
        
        $company = new CompanyWithMultiPropDepth1();
        
        // getTeam() triggers lazy loading via MultiPropDataSource
        $team = $company->getTeam();
        
        // Team should be hydrated with scalars
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Engineering Team', $team->getName());
        
        // Leader should be hydrated (depth=1 allows direct children)
        $leader = $team->getLeader();
        $this->assertInstanceOf(Employee::class, $leader);
        $this->assertEquals('Alice Manager', $leader->getName());
        $this->assertEquals('Tech Lead', $leader->getRole());
        
        // Cross-hydration should work - company scalars should be set
        $this->assertEquals('Acme Corporation', $company->getName());
        $this->assertEquals('ACME-001', $company->getCode());
    }
}
