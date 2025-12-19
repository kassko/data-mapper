<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\Needs;
use PHPUnit\Framework\TestCase;

class NeedsTest extends TestCase
{
    public function testNeedsAttributeCanBeCreatedWithEmptyProperties(): void
    {
        $needs = new Needs();
        
        $this->assertSame([], $needs->properties);
    }
    
    public function testNeedsAttributeCanBeCreatedWithProperties(): void
    {
        $needs = new Needs(['propA', 'propB', 'propC']);
        
        $this->assertSame(['propA', 'propB', 'propC'], $needs->properties);
    }
    
    public function testNeedsAttributeCanBeReadFromReflection(): void
    {
        $class = new class {
            #[Needs(['prop1', 'prop2'])]
            private mixed $prop3 = null;
        };
        
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty('prop3');
        $attributes = $property->getAttributes(Needs::class);
        
        $this->assertCount(1, $attributes);
        
        $needs = $attributes[0]->newInstance();
        $this->assertInstanceOf(Needs::class, $needs);
        $this->assertSame(['prop1', 'prop2'], $needs->properties);
    }
}
