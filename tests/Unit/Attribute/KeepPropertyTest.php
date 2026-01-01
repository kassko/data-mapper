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

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\KeepProperty;
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\SkipProperty;
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
        $testClass = new #[SkipAllProperties] class {
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

    public function testKeepPropertyWithWhenExpression(): void
    {
        $testClass = new class {
            #[KeepProperty(when: "expr(contextKeyExists('keep_me'))")]
            private ?string $conditionalProp = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('conditionalProp');

        $keepProperty = $this->reader->readKeepProperty($property);

        $this->assertInstanceOf(KeepProperty::class, $keepProperty);
        $this->assertEquals("expr(contextKeyExists('keep_me'))", $keepProperty->when);
    }

    public function testKeepPropertyWithoutWhenExpression(): void
    {
        $testClass = new class {
            #[KeepProperty]
            private ?string $alwaysKept = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('alwaysKept');

        $keepProperty = $this->reader->readKeepProperty($property);

        $this->assertInstanceOf(KeepProperty::class, $keepProperty);
        $this->assertNull($keepProperty->when);
    }

    public function testSkipPropertyWithWhenExpression(): void
    {
        $testClass = new class {
            #[SkipProperty(when: "expr(contextKeyExists('skip_me'))")]
            private ?string $conditionalSkip = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('conditionalSkip');

        $skipProperty = $this->reader->readSkipProperty($property);

        $this->assertInstanceOf(SkipProperty::class, $skipProperty);
        $this->assertEquals("expr(contextKeyExists('skip_me'))", $skipProperty->when);
    }

    public function testSkipPropertyWithoutWhenExpression(): void
    {
        $testClass = new class {
            #[SkipProperty]
            private ?string $alwaysSkipped = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('alwaysSkipped');

        $skipProperty = $this->reader->readSkipProperty($property);

        $this->assertInstanceOf(SkipProperty::class, $skipProperty);
        $this->assertNull($skipProperty->when);
    }

    public function testGetHydrationDecisionInfo(): void
    {
        $testClass = new #[SkipAllProperties] class {
            #[KeepProperty(when: "expr(contextKeyExists('keep'))")]
            private ?string $conditionalKeep = null;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('conditionalKeep');

        $decisionInfo = $this->reader->getHydrationDecisionInfo($reflection, $property);

        $this->assertArrayHasKey('skipWhen', $decisionInfo);
        $this->assertArrayHasKey('keepWhen', $decisionInfo);
        $this->assertArrayHasKey('propertyKeepWhen', $decisionInfo);
        $this->assertArrayHasKey('hasSkipProperty', $decisionInfo);
        $this->assertArrayHasKey('hasProperty', $decisionInfo);
        $this->assertArrayHasKey('hasKeepProperty', $decisionInfo);
        $this->assertArrayHasKey('hasSkipAllProperties', $decisionInfo);
        
        $this->assertEquals("expr(contextKeyExists('keep'))", $decisionInfo['keepWhen']);
        $this->assertTrue($decisionInfo['hasKeepProperty']);
        $this->assertTrue($decisionInfo['hasSkipAllProperties']);
    }
}
