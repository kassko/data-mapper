<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\Context;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\DataMapper\Registry\ContextRegistry;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ContextTest extends TestCase
{
    private AttributeReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AttributeReader();
        // Clear context before each test
        ContextRegistry::clear();
    }

    protected function tearDown(): void
    {
        // Clear context after each test
        ContextRegistry::clear();
    }

    public function testContextAttributeWithNamedArguments(): void
    {
        $testClass = new class {
            #[Context(key1: 'value1', key2: 'value2')]
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $context = $this->reader->readContext($property);

        $this->assertInstanceOf(Context::class, $context);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $context->values);
    }

    public function testContextAttributeNotPresentReturnsNull(): void
    {
        $testClass = new class {
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $context = $this->reader->readContext($property);

        $this->assertNull($context);
    }

    public function testContextRegistrySetAndGet(): void
    {
        ContextRegistry::set('test_key', 'test_value');

        $this->assertTrue(ContextRegistry::has('test_key'));
        $this->assertEquals('test_value', ContextRegistry::get('test_key'));
    }

    public function testContextRegistrySetMany(): void
    {
        ContextRegistry::setMany([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertEquals('value1', ContextRegistry::get('key1'));
        $this->assertEquals('value2', ContextRegistry::get('key2'));
    }

    public function testContextRegistryGetWithDefault(): void
    {
        $this->assertEquals('default', ContextRegistry::get('non_existent', 'default'));
    }

    public function testContextRegistryClear(): void
    {
        ContextRegistry::set('key', 'value');
        $this->assertTrue(ContextRegistry::has('key'));

        ContextRegistry::clear();
        $this->assertFalse(ContextRegistry::has('key'));
    }

    public function testContextRegistryAll(): void
    {
        ContextRegistry::setMany([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $all = ContextRegistry::all();
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('key1', $all);
        $this->assertArrayHasKey('key2', $all);
    }
}
