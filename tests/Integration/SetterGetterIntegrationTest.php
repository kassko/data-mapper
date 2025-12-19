<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Attribute\Setter;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\LazyLoader\LazyLoader;
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

        $lazyLoader = new LazyLoader();
        $mapper = new DataMapper($lazyLoader);

        $data = ['firstName' => 'john'];
        $mapper->hydrate($testClass, $data);

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

        $lazyLoader = new LazyLoader();
        $mapper = new DataMapper($lazyLoader);

        $data = ['name' => 'jane'];
        $mapper->hydrate($testClass, $data);

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

        $lazyLoader = new LazyLoader();
        $mapper = new DataMapper($lazyLoader);

        $data = ['emails' => ['email1@test.com', 'email2@test.com', 'email3@test.com']];
        $mapper->hydrate($testClass, $data);

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

        $lazyLoader = new LazyLoader();
        $mapper = new DataMapper($lazyLoader);

        $data = ['name' => 'direct'];
        $mapper->hydrate($testClass, $data);

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

        $lazyLoader = new LazyLoader();
        $mapper = new DataMapper($lazyLoader);

        // Associative array should not use adder
        $data = ['config' => ['key1' => 'value1', 'key2' => 'value2']];
        $mapper->hydrate($testClass, $data);

        $this->assertFalse($testClass->wasAdderCalled());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $testClass->getConfig());
    }
}
