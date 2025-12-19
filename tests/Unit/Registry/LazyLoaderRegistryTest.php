<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit\Registry;

use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\DataMapper\LazyLoader\LazyLoaderInterface;
use PHPUnit\Framework\TestCase;

final class LazyLoaderRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testSetAndGet(): void
    {
        $loader = $this->createMock(LazyLoaderInterface::class);
        
        LazyLoaderRegistry::set($loader);
        
        $this->assertSame($loader, LazyLoaderRegistry::get());
    }

    public function testHasReturnsFalseWhenEmpty(): void
    {
        $this->assertFalse(LazyLoaderRegistry::has());
    }

    public function testHasReturnsTrueWhenSet(): void
    {
        $loader = $this->createMock(LazyLoaderInterface::class);
        LazyLoaderRegistry::set($loader);
        
        $this->assertTrue(LazyLoaderRegistry::has());
    }

    public function testClear(): void
    {
        $loader = $this->createMock(LazyLoaderInterface::class);
        LazyLoaderRegistry::set($loader);
        LazyLoaderRegistry::clear();
        
        $this->assertFalse(LazyLoaderRegistry::has());
    }
}
