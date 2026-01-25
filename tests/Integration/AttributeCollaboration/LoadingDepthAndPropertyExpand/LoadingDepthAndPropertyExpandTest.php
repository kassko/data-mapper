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

namespace Kassko\DataMapper\Tests\Integration\AttributeCollaboration\LoadingDepthAndPropertyExpand;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\LoadingDepthExpand\CompanyWithDepth0AndExpand;
use Kassko\Sample\LoadingDepthExpand\CompanyWithDepth1AndExpand;
use Kassko\Sample\LoadingDepthExpand\CompanyWithDepth1AndNoExpand;
use Kassko\Sample\LoadingDepthExpand\Employee;
use Kassko\Sample\LoadingDepthExpand\Team;
use PHPUnit\Framework\TestCase;

/**
 * Tests for collaboration between Loading::depth and Property::expand/noExpand.
 * 
 * The depth attribute controls how deep recursive hydration goes, while
 * expand/noExpand filters which properties are hydrated at each level.
 * 
 * Order of evaluation:
 * 1. depth is checked first - if exceeded, skip the property entirely
 * 2. expand/noExpand filters which properties to hydrate within the depth limit
 */
class LoadingDepthAndPropertyExpandTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    /**
     * Test depth=1 with expand filter.
     * 
     * With depth=1, Team and its direct children can be hydrated.
     * With expand="name,code,manager", only those properties should be hydrated.
     * assistant and members should NOT be hydrated (filtered by expand).
     */
    public function testDepth1WithExpandFiltersProperties(): void
    {
        new DataMapper(new ServiceResolver());
        
        $company = new CompanyWithDepth1AndExpand();
        $company->setName('Test Corp');
        
        // getTeam() triggers lazy loading
        $team = $company->getTeam();
        
        // Team should be hydrated
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Engineering Team', $team->getName());
        $this->assertEquals('ENG-001', $team->getCode());
        
        // Manager should be hydrated (in expand list and within depth)
        $manager = $team->getManager();
        $this->assertInstanceOf(Employee::class, $manager);
        $this->assertEquals('Alice Manager', $manager->getName());
        $this->assertEquals('Engineering Manager', $manager->getRole());
        
        // Assistant should NOT be hydrated (not in expand list)
        $assistant = $team->getAssistant();
        $this->assertNull($assistant, 'assistant should not be hydrated - not in expand list');
        
        // Members should NOT be hydrated (not in expand list)
        $members = $team->getMembers();
        $this->assertEmpty($members, 'members should not be hydrated - not in expand list');
    }

    /**
     * Test depth=1 with noExpand filter.
     * 
     * With depth=1, Team and its direct children can be hydrated.
     * With noExpand="members", all properties EXCEPT members should be hydrated.
     */
    public function testDepth1WithNoExpandFiltersProperties(): void
    {
        new DataMapper(new ServiceResolver());
        
        $company = new CompanyWithDepth1AndNoExpand();
        $company->setName('Test Corp');
        
        // getTeam() triggers lazy loading
        $team = $company->getTeam();
        
        // Team should be hydrated
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Engineering Team', $team->getName());
        $this->assertEquals('ENG-001', $team->getCode());
        
        // Manager should be hydrated (not in noExpand list and within depth)
        $manager = $team->getManager();
        $this->assertInstanceOf(Employee::class, $manager);
        $this->assertEquals('Alice Manager', $manager->getName());
        
        // Assistant should be hydrated (not in noExpand list)
        $assistant = $team->getAssistant();
        $this->assertInstanceOf(Employee::class, $assistant);
        $this->assertEquals('Bob Assistant', $assistant->getName());
        
        // Members should NOT be hydrated (in noExpand list)
        $members = $team->getMembers();
        $this->assertEmpty($members, 'members should not be hydrated - in noExpand list');
    }

    /**
     * Test depth=0 with expand filter - depth takes precedence.
     * 
     * With depth=0, only Team scalars are hydrated, no nested objects.
     * Even though expand includes "manager", it should NOT be hydrated
     * because depth=0 prevents nested object hydration.
     * 
     * This proves that depth is checked BEFORE expand filtering.
     */
    public function testDepth0TakesPrecedenceOverExpand(): void
    {
        new DataMapper(new ServiceResolver());
        
        $company = new CompanyWithDepth0AndExpand();
        $company->setName('Test Corp');
        
        // getTeam() triggers lazy loading
        $team = $company->getTeam();
        
        // Team should be hydrated with scalars
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('Engineering Team', $team->getName());
        $this->assertEquals('ENG-001', $team->getCode());
        
        // Manager should NOT be hydrated (depth=0 prevents nested objects)
        // Even though it's in the expand list!
        $manager = $team->getManager();
        $this->assertNull($manager, 'manager should not be hydrated - depth=0 prevents nested objects');
        
        // Assistant should NOT be hydrated
        $assistant = $team->getAssistant();
        $this->assertNull($assistant, 'assistant should not be hydrated - depth=0');
        
        // Members should NOT be hydrated
        $members = $team->getMembers();
        $this->assertEmpty($members, 'members should not be hydrated - depth=0');
    }
}
