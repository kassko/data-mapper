<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\Person;
use PHPUnit\Framework\TestCase;

final class RegistryIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testLazyLoadingWorksWithoutPrepare(): void
    {
        // Build DataMapper once
        (new DataMapperBuilder())->build();
        
        // Create object - NO prepare() call!
        $person = new Person(1);
        
        // Lazy loading should work
        $name = $person->getName();
        
        $this->assertNotNull($name);
        $this->assertEquals('foo', $name);
    }

    public function testObjectIsSerializable(): void
    {
        (new DataMapperBuilder())->build();
        
        $person = new Person(1);
        
        // Object should be serializable (no service in properties)
        $serialized = serialize($person);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(Person::class, $unserialized);
    }

    public function testMultipleObjects(): void
    {
        (new DataMapperBuilder())->build();
        
        $person1 = new Person(1);
        $person2 = new Person(2);
        
        $this->assertEquals('foo', $person1->getName());
        $this->assertEquals('bar', $person2->getName());
    }

    public function testWithoutDataMapperGracefulDegradation(): void
    {
        // Don't build DataMapper - objects should still work but properties won't load
        LazyLoaderRegistry::clear();
        
        $person = new Person(1);
        
        // Accessing properties should not throw - just return null
        $name = $person->getName();
        
        $this->assertNull($name);
    }
}
