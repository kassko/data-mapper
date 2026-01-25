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

namespace Kassko\DataMapper\Tests\Integration\Attributes\LoadingCheckDepth;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\LoadingDepth\Department;
use Kassko\Sample\LoadingDepth\Employee;
use Kassko\Sample\LoadingDepth\OrganizationWithDepth0;
use Kassko\Sample\LoadingDepth\OrganizationWithDepth1;
use Kassko\Sample\LoadingDepth\OrganizationWithDepth2;
use Kassko\Sample\LoadingDepth\OrganizationWithNoLimit;
use Kassko\Sample\LoadingDepth\Team;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Loading::depth attribute functionality.
 * 
 * The depth attribute controls how deep recursive hydration should go:
 * - null: No limit, hydrate all nested levels
 * - 0: Hydrate only the root property itself
 * - 1: Hydrate root + direct children
 * - 2: Hydrate root + children + grandchildren
 * - etc.
 */
class DepthControlTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    /**
     * Test that depth=0 hydrates only the root object (Department)
     * but leaves nested objects (Team) as arrays.
     */
    public function testDepth0HydratesOnlyRootObject(): void
    {
        new DataMapper(new ServiceResolver());
        
        $org = new OrganizationWithDepth0();
        $org->setName('Test Corp');
        
        // getDepartment() triggers lazy loading via loadProperty('department')
        $department = $org->getDepartment();
        
        // Department should be hydrated
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Engineering', $department->getName());
        $this->assertEquals('ENG-001', $department->getCode());
        
        // mainTeam should NOT be hydrated (should be null or array, not Team instance)
        // With depth=0, we stop at the Department level
        $mainTeam = $department->getMainTeam();
        $this->assertNull($mainTeam, 'With depth=0, mainTeam should not be hydrated into a Team object');
    }

    /**
     * Test that depth=1 hydrates root + direct children.
     * Department (0) -> Team (1) should be hydrated
     * Team -> Employee (2) should NOT be hydrated
     */
    public function testDepth1HydratesRootAndDirectChildren(): void
    {
        new DataMapper(new ServiceResolver());
        
        $org = new OrganizationWithDepth1();
        $org->setName('Test Corp');
        
        // getDepartment() triggers lazy loading
        $department = $org->getDepartment();
        
        // Department should be hydrated
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Engineering', $department->getName());
        
        // Team should be hydrated (depth 1)
        $team = $department->getMainTeam();
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Backend Team', $team->getName());
        
        // Leader (Employee) should NOT be hydrated (depth 2)
        $leader = $team->getLeader();
        $this->assertNull($leader, 'With depth=1, leader should not be hydrated into an Employee object');
        
        // Members should NOT be hydrated
        $members = $team->getMembers();
        $this->assertEmpty($members, 'With depth=1, members should not be hydrated');
    }

    /**
     * Test that depth=2 hydrates root + children + grandchildren.
     * Department (0) -> Team (1) -> Employee (2) should all be hydrated.
     */
    public function testDepth2HydratesThreeLevels(): void
    {
        new DataMapper(new ServiceResolver());
        
        $org = new OrganizationWithDepth2();
        $org->setName('Test Corp');
        
        // getDepartment() triggers lazy loading
        $department = $org->getDepartment();
        
        // Department should be hydrated
        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Engineering', $department->getName());
        
        // Team should be hydrated
        $team = $department->getMainTeam();
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Backend Team', $team->getName());
        
        // Leader should be hydrated (depth 2)
        $leader = $team->getLeader();
        $this->assertInstanceOf(Employee::class, $leader);
        $this->assertEquals('Alice Johnson', $leader->getName());
        $this->assertEquals('Tech Lead', $leader->getRole());
        
        // Members should also be hydrated
        $members = $team->getMembers();
        $this->assertCount(2, $members);
        $this->assertInstanceOf(Employee::class, $members[0]);
        $this->assertEquals('Bob Smith', $members[0]->getName());
        $this->assertInstanceOf(Employee::class, $members[1]);
        $this->assertEquals('Carol White', $members[1]->getName());
    }

    /**
     * Test that depth=null (no limit) hydrates all levels.
     */
    public function testNoDepthLimitHydratesAllLevels(): void
    {
        new DataMapper(new ServiceResolver());
        
        $org = new OrganizationWithNoLimit();
        $org->setName('Test Corp');
        
        // getDepartment() triggers lazy loading
        $department = $org->getDepartment();
        
        // All levels should be hydrated
        $this->assertInstanceOf(Department::class, $department);
        
        $team = $department->getMainTeam();
        $this->assertInstanceOf(Team::class, $team);
        
        $leader = $team->getLeader();
        $this->assertInstanceOf(Employee::class, $leader);
        $this->assertEquals('Alice Johnson', $leader->getName());
        
        $members = $team->getMembers();
        $this->assertCount(2, $members);
        $this->assertInstanceOf(Employee::class, $members[0]);
        $this->assertInstanceOf(Employee::class, $members[1]);
    }
}
