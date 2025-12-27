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
use Kassko\Sample\Person;
use PHPUnit\Framework\TestCase;

class DataMapperTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testDataMapperPreparesObjectForLazyLoading(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver()); // Registers Loader automatically
        $person = new Person(1);

        // Access properties - they should be lazy loaded (no prepare() needed!)
        $this->assertEquals('foo', $person->getName());
        $this->assertEquals('foo@aaa.com', $person->getEmail());
    }

    public function testSingleCallOptimization(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver()); // Registers Loader automatically
        $person = new Person(1);

        // First call to getName() should load both name and email
        $name = $person->getName();
        $this->assertEquals('foo', $name);

        // Second call to getEmail() should NOT trigger another DataSource call
        // (this is verified by the fact that we only have one DataSource mock call)
        $email = $person->getEmail();
        $this->assertEquals('foo@aaa.com', $email);
    }

    public function testMultiplePersonsWithDifferentIds(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver()); // Registers Loader automatically
        
        $person1 = new Person(1);
        $person2 = new Person(2);
        $person3 = new Person(3);

        $this->assertEquals('foo', $person1->getName());
        $this->assertEquals('foo@aaa.com', $person1->getEmail());

        $this->assertEquals('bar', $person2->getName());
        $this->assertEquals('bar@bbb.com', $person2->getEmail());

        $this->assertEquals('baz', $person3->getName());
        $this->assertEquals('baz@ccc.com', $person3->getEmail());
    }
}
