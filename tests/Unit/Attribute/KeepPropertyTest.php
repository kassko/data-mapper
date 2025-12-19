<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\KeepProperty;
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Metadata\AttributeReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class KeepPropertyTest extends TestCase
{
    private AttributeReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AttributeReader();
    }

    public function testKeepPropertyAttributeExists(): void
    {
        $testClass = new class {
            #[KeepProperty]
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $keepProperty = $this->reader->readKeepProperty($property);

        $this->assertInstanceOf(KeepProperty::class, $keepProperty);
    }

    public function testKeepPropertyAttributeNotPresentReturnsNull(): void
    {
        $testClass = new class {
            private ?string $name = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('name');

        $keepProperty = $this->reader->readKeepProperty($property);

        $this->assertNull($keepProperty);
    }

    public function testKeepPropertyWorksWithSkipAllProperties(): void
    {
        #[SkipAllProperties]
        $testClass = new class {
            #[KeepProperty]
            private ?string $included = null;

            private ?string $excluded = null;
        };

        $reflection = new ReflectionClass($testClass);

        $includedProperty = $reflection->getProperty('included');
        $shouldHydrateIncluded = $this->reader->shouldHydrateProperty($reflection, $includedProperty);
        $this->assertTrue($shouldHydrateIncluded);

        $excludedProperty = $reflection->getProperty('excluded');
        $shouldHydrateExcluded = $this->reader->shouldHydrateProperty($reflection, $excludedProperty);
        $this->assertFalse($shouldHydrateExcluded);
    }
}
