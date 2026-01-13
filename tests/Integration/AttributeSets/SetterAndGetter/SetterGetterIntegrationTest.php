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

use Kassko\DataMapper\Attribute\Setter;
use PHPUnit\Framework\TestCase;

class SetterGetterIntegrationTest extends TestCase
{
    public function testSetterAttributeUsesCustomMethod(): void
    {
        $testClass = new class {
            private string $methodCalled = '';
            
            #[Setter(name: 'setCustomName')]
            private ?string $firstName = null;

            public function setCustomName(string $name): void
            {
                $this->methodCalled = 'setCustomName';
                $this->firstName = strtoupper($name);
            }

            public function getMethodCalled(): string
            {
                return $this->methodCalled;
            }

            public function getFirstName(): ?string
            {
                return $this->firstName;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        $data = ['firstName' => 'john'];
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertEquals('setCustomName', $testClass->getMethodCalled());
        $this->assertEquals('JOHN', $testClass->getFirstName());
    }

    public function testSetterFallsBackToConventionalSetter(): void
    {
        $testClass = new class {
            private string $methodCalled = '';
            private ?string $name = null;

            public function setName(string $name): void
            {
                $this->methodCalled = 'setName';
                $this->name = strtoupper($name);
            }

            public function getMethodCalled(): string
            {
                return $this->methodCalled;
            }

            public function getName(): ?string
            {
                return $this->name;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        $data = ['name' => 'jane'];
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertEquals('setName', $testClass->getMethodCalled());
        $this->assertEquals('JANE', $testClass->getName());
    }

    public function testAdderMethodForListArrays(): void
    {
        $testClass = new class {
            private array $emails = [];
            private array $methodCalls = [];

            public function addEmailsItem(string $email): void
            {
                $this->methodCalls[] = 'addEmailsItem';
                $this->emails[] = $email;
            }

            public function getEmails(): array
            {
                return $this->emails;
            }

            public function getMethodCalls(): array
            {
                return $this->methodCalls;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        $data = ['emails' => ['email1@test.com', 'email2@test.com', 'email3@test.com']];
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertCount(3, $testClass->getMethodCalls());
        $this->assertEquals(['email1@test.com', 'email2@test.com', 'email3@test.com'], $testClass->getEmails());
    }

    public function testSetterFallsBackToDirectAssignment(): void
    {
        $testClass = new class {
            private ?string $name = null;

            public function getName(): ?string
            {
                return $this->name;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        $data = ['name' => 'direct'];
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertEquals('direct', $testClass->getName());
    }

    public function testAssociativeArrayDoesNotUseAdder(): void
    {
        $testClass = new class {
            private array $config = [];
            private bool $adderCalled = false;

            public function addConfigItem(string $value): void
            {
                $this->adderCalled = true;
                $this->config[] = $value;
            }

            public function setConfig(array $config): void
            {
                $this->config = $config;
            }

            public function getConfig(): array
            {
                return $this->config;
            }

            public function wasAdderCalled(): bool
            {
                return $this->adderCalled;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        // Associative array should not use adder
        $data = ['config' => ['key1' => 'value1', 'key2' => 'value2']];
        
        // Use reflection to call the private hydrateObject method
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertFalse($testClass->wasAdderCalled());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $testClass->getConfig());
    }

    public function testExplicitAdderWithSetterAttribute(): void
    {
        $testClass = new class {
            use SetterTrait;
            
            private array $items = [];
            private array $methodCalls = [];

            #[\Kassko\DataMapper\Attribute\Setter(name: 'addItem', type: \Kassko\DataMapper\Attribute\Setter::TYPE_ADDER)]
            private array $collection = [];

            public function addItem(mixed $item): void
            {
                $this->methodCalls[] = 'addItem';
                $this->collection[] = $item;
            }

            public function getCollection(): array
            {
                return $this->collection;
            }

            public function getMethodCalls(): array
            {
                return $this->methodCalls;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        $data = ['collection' => ['item1', 'item2', 'item3']];
        
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertCount(3, $testClass->getMethodCalls());
        $this->assertEquals(['item1', 'item2', 'item3'], $testClass->getCollection());
    }

    public function testIndexedAdderWithSetterAttribute(): void
    {
        $testClass = new class {
            use SetterTrait;
            
            private array $methodCalls = [];

            #[\Kassko\DataMapper\Attribute\Setter(name: 'addAddress', type: \Kassko\DataMapper\Attribute\Setter::TYPE_INDEXED_ADDER)]
            private array $addresses = [];

            public function addAddress(mixed $index, mixed $address): void
            {
                $this->methodCalls[] = ['method' => 'addAddress', 'index' => $index, 'value' => $address];
                $this->addresses[$index] = $address;
            }

            public function getAddresses(): array
            {
                return $this->addresses;
            }

            public function getMethodCalls(): array
            {
                return $this->methodCalls;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        // Associative array - indexed adder should preserve keys
        $data = ['addresses' => ['home' => '123 Main St', 'work' => '456 Office Blvd']];
        
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertCount(2, $testClass->getMethodCalls());
        $this->assertEquals(['home' => '123 Main St', 'work' => '456 Office Blvd'], $testClass->getAddresses());
        
        // Verify the correct index was passed to each call
        $this->assertEquals('home', $testClass->getMethodCalls()[0]['index']);
        $this->assertEquals('work', $testClass->getMethodCalls()[1]['index']);
    }

    public function testIndexedAdderWithListArray(): void
    {
        $testClass = new class {
            use SetterTrait;
            
            private array $methodCalls = [];

            #[\Kassko\DataMapper\Attribute\Setter(name: 'addTag', type: \Kassko\DataMapper\Attribute\Setter::TYPE_INDEXED_ADDER)]
            private array $tags = [];

            public function addTag(mixed $index, mixed $tag): void
            {
                $this->methodCalls[] = ['method' => 'addTag', 'index' => $index, 'value' => $tag];
                $this->tags[$index] = $tag;
            }

            public function getTags(): array
            {
                return $this->tags;
            }

            public function getMethodCalls(): array
            {
                return $this->methodCalls;
            }
        };

        $lazyLoader = new \Kassko\DataMapper\Loader\Loader(new \Kassko\DataMapper\ServiceResolver());

        // List array - indexed adder should get numeric indexes
        $data = ['tags' => ['php', 'symfony', 'datamapper']];
        
        $reflection = new \ReflectionClass($lazyLoader);
        $method = $reflection->getMethod('hydrateObject');
        $method->invoke($lazyLoader, $testClass, $data, null, 0);

        $this->assertCount(3, $testClass->getMethodCalls());
        $this->assertEquals([0 => 'php', 1 => 'symfony', 2 => 'datamapper'], $testClass->getTags());
        
        // Verify numeric indexes were passed
        $this->assertEquals(0, $testClass->getMethodCalls()[0]['index']);
        $this->assertEquals(1, $testClass->getMethodCalls()[1]['index']);
        $this->assertEquals(2, $testClass->getMethodCalls()[2]['index']);
    }
}

// Helper trait to avoid reflection issues with anonymous classes
trait SetterTrait {}
