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

namespace Kassko\DataMapper\Tests\Unit\Registry;

use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Loader\LoaderInterface;
use PHPUnit\Framework\TestCase;

final class LoaderRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        LoaderRegistry::clear();
    }

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testSetAndGet(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        
        LoaderRegistry::set($loader);
        
        $this->assertSame($loader, LoaderRegistry::get());
    }

    public function testHasReturnsFalseWhenEmpty(): void
    {
        $this->assertFalse(LoaderRegistry::has());
    }

    public function testHasReturnsTrueWhenSet(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        LoaderRegistry::set($loader);
        
        $this->assertTrue(LoaderRegistry::has());
    }

    public function testClear(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        LoaderRegistry::set($loader);
        LoaderRegistry::clear();
        
        $this->assertFalse(LoaderRegistry::has());
    }
}
