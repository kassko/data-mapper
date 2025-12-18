<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\LazyLoader\LazyLoader;
use Kassko\Sample\Person;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LazyLoaderTest extends TestCase
{
    public function testLoadPropertyHydratesProperty(): void
    {
        $loader = new LazyLoader();
        $person = new Person(1);

        // Verify property is null before loading
        $reflection = new ReflectionClass($person);
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);
        $this->assertNull($nameProperty->getValue($person));

        // Load the property
        $loader->loadProperty($person, 'name');

        // Verify property is hydrated
        $this->assertEquals('foo', $nameProperty->getValue($person));
    }

    public function testLoadPropertyHydratesAllPropertiesWithSameSignature(): void
    {
        $loader = new LazyLoader();
        $person = new Person(1);

        // Load only 'name' property
        $loader->loadProperty($person, 'name');

        // Verify both 'name' and 'email' are hydrated (same signature)
        $reflection = new ReflectionClass($person);
        
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);
        $this->assertEquals('foo', $nameProperty->getValue($person));

        $emailProperty = $reflection->getProperty('email');
        $emailProperty->setAccessible(true);
        $this->assertEquals('foo@aaa.com', $emailProperty->getValue($person));
    }

    public function testLoadPropertyDoesNotReloadAlreadyLoadedProperty(): void
    {
        $loader = new LazyLoader();
        $person = new Person(1);

        // Load the property once
        $loader->loadProperty($person, 'name');

        // Get the value
        $reflection = new ReflectionClass($person);
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);
        $firstValue = $nameProperty->getValue($person);

        // Manually change the value
        $nameProperty->setValue($person, 'modified');

        // Try to load again
        $loader->loadProperty($person, 'name');

        // Verify it wasn't reloaded (value should still be 'modified')
        $this->assertEquals('modified', $nameProperty->getValue($person));
    }

    public function testDifferentObjectsAreLoadedIndependently(): void
    {
        $loader = new LazyLoader();
        $person1 = new Person(1);
        $person2 = new Person(2);

        $loader->loadProperty($person1, 'name');
        $loader->loadProperty($person2, 'name');

        $reflection = new ReflectionClass($person1);
        
        $person1Name = $reflection->getProperty('name');
        $person1Name->setAccessible(true);
        $this->assertEquals('foo', $person1Name->getValue($person1));

        $person2Name = $reflection->getProperty('name');
        $person2Name->setAccessible(true);
        $this->assertEquals('bar', $person2Name->getValue($person2));
    }
}
