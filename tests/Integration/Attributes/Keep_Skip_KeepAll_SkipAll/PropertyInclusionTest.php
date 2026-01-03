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

use Kassko\DataMapper\Attribute\KeepProperty;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\SkipAllProperties;
use Kassko\DataMapper\Attribute\SkipProperty;
use Kassko\DataMapper\Loader\Loader;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\Sample\KeepSkip\ProductWithSkip;
use Kassko\Sample\KeepSkip\ProductWithSkipAll;
use PHPUnit\Framework\TestCase;

class PropertyInclusionTest extends TestCase
{
    protected function tearDown(): void
    {
        ContextRegistry::clear();
    }
    public function testSkipPropertyIsNotHydrated(): void
    {
        $lazyLoader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $data = [
            'name' => 'Widget',
            'internalNote' => 'Secret note',
            'price' => 19.99
        ];
        
        $product = new ProductWithSkip();
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $product, $data, null, 0);
        
        // name and price should be hydrated
        $this->assertEquals('Widget', $product->getName());
        $this->assertEquals(19.99, $product->getPrice());
        
        // internalNote should NOT be hydrated (marked with SkipProperty)
        $this->assertNull($product->getInternalNote());
    }

    public function testSkipAllPropertiesOnlyHydratesMarkedProperties(): void
    {
        $lazyLoader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $data = [
            'name' => 'Gadget',
            'description' => 'A wonderful gadget',
            'price' => 29.99
        ];
        
        $product = new ProductWithSkipAll();
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $product, $data, null, 0);
        
        // Only description should be hydrated (marked with Property)
        $this->assertEquals('A wonderful gadget', $product->getDescription());
        
        // name and price should NOT be hydrated (class has SkipAllProperties)
        $this->assertNull($product->getName());
        $this->assertNull($product->getPrice());
    }

    public function testSkipPropertyWithWhenExpressionTrue(): void
    {
        // Set context so that the 'when' expression evaluates to true
        ContextRegistry::set('skip_email', true);
        
        $loader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $entity = new class {
            private ?string $firstName = null;
            #[SkipProperty(when: "expr(contextKeyExists('skip_email'))")]
            private ?string $email = null;
            
            public function getFirstName(): ?string { return $this->firstName; }
            public function getEmail(): ?string { return $this->email; }
        };
        
        $data = [
            'firstName' => 'John',
            'email' => 'john@example.com',
        ];
        
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $entity, $data, null, 0);
        
        // firstName should be hydrated
        $this->assertEquals('John', $entity->getFirstName());
        // email should NOT be hydrated (when expression is true)
        $this->assertNull($entity->getEmail());
    }

    public function testSkipPropertyWithWhenExpressionFalse(): void
    {
        // Do NOT set context so that the 'when' expression evaluates to false
        // ContextRegistry::set('skip_email', true);  <- not set
        
        $loader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $entity = new class {
            private ?string $firstName = null;
            #[SkipProperty(when: "expr(contextKeyExists('skip_email'))")]
            private ?string $email = null;
            
            public function getFirstName(): ?string { return $this->firstName; }
            public function getEmail(): ?string { return $this->email; }
        };
        
        $data = [
            'firstName' => 'John',
            'email' => 'john@example.com',
        ];
        
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $entity, $data, null, 0);
        
        // Both should be hydrated (when expression is false)
        $this->assertEquals('John', $entity->getFirstName());
        $this->assertEquals('john@example.com', $entity->getEmail());
    }

    public function testKeepPropertyWithWhenExpressionTrue(): void
    {
        // Set context so that the 'when' expression evaluates to true
        ContextRegistry::set('keep_id', true);
        
        $loader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $entity = new #[SkipAllProperties] class {
            #[KeepProperty(when: "expr(contextKeyExists('keep_id'))")]
            private ?string $id = null;
            private ?string $temp = null;
            
            public function getId(): ?string { return $this->id; }
            public function getTemp(): ?string { return $this->temp; }
        };
        
        $data = [
            'id' => '123',
            'temp' => 'temporary',
        ];
        
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $entity, $data, null, 0);
        
        // id should be hydrated (KeepProperty with when=true)
        $this->assertEquals('123', $entity->getId());
        // temp should NOT be hydrated (no KeepProperty, class has SkipAllProperties)
        $this->assertNull($entity->getTemp());
    }

    public function testKeepPropertyWithWhenExpressionFalse(): void
    {
        // Do NOT set context so that the 'when' expression evaluates to false
        
        $loader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $entity = new #[SkipAllProperties] class {
            #[KeepProperty(when: "expr(contextKeyExists('keep_id'))")]
            private ?string $id = null;
            private ?string $temp = null;
            
            public function getId(): ?string { return $this->id; }
            public function getTemp(): ?string { return $this->temp; }
        };
        
        $data = [
            'id' => '123',
            'temp' => 'temporary',
        ];
        
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $entity, $data, null, 0);
        
        // Neither should be hydrated (KeepProperty when=false, class has SkipAllProperties)
        $this->assertNull($entity->getId());
        $this->assertNull($entity->getTemp());
    }

    public function testPropertyKeepWhenTrue(): void
    {
        // Set context so that the 'keepWhen' expression evaluates to true
        ContextRegistry::set('keep_name', true);
        
        $loader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $entity = new #[SkipAllProperties] class {
            #[Property(name: 'user_name', keepWhen: "expr(contextKeyExists('keep_name'))")]
            private ?string $name = null;
            private ?string $temp = null;
            
            public function getName(): ?string { return $this->name; }
            public function getTemp(): ?string { return $this->temp; }
        };
        
        $data = [
            'user_name' => 'John',
            'temp' => 'temporary',
        ];
        
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $entity, $data, null, 0);
        
        // name should be hydrated (Property with keepWhen=true)
        $this->assertEquals('John', $entity->getName());
        // temp should NOT be hydrated (no Property, class has SkipAllProperties)
        $this->assertNull($entity->getTemp());
    }

    public function testPropertyKeepWhenFalse(): void
    {
        // Do NOT set context so that the 'keepWhen' expression evaluates to false
        
        $loader = new Loader(new \Kassko\DataMapper\ServiceResolver());
        
        $entity = new #[SkipAllProperties] class {
            #[Property(name: 'user_name', keepWhen: "expr(contextKeyExists('keep_name'))")]
            private ?string $name = null;
            private ?string $temp = null;
            
            public function getName(): ?string { return $this->name; }
            public function getTemp(): ?string { return $this->temp; }
        };
        
        $data = [
            'user_name' => 'John',
            'temp' => 'temporary',
        ];
        
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($loader, $entity, $data, null, 0);
        
        // Neither should be hydrated (Property keepWhen=false, class has SkipAllProperties)
        $this->assertNull($entity->getName());
        $this->assertNull($entity->getTemp());
    }
}
