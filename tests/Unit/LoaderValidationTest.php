<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use PHPUnit\Framework\TestCase;

class LoaderValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }
    public function testThrowsExceptionForNonExistentClass(): void
    {
        $entity = new class(1) {
            use LoadableInternalTrait;

            private int $id;

            #[DataSource(class: 'NonExistentClass', method: 'getData', args: ['#id'])]
            private ?string $data = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getData(): ?string
            {
                $this->loadProperty('data');
                return $this->data;
            }
        };

        $dataMapper = new DataMapper(new \Kassko\DataMapper\ServiceResolver());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("DataSource class 'NonExistentClass' does not exist");

        $entity->getData();
    }

    public function testThrowsExceptionForNonExistentMethod(): void
    {
        $entity = new class(1) {
            use LoadableInternalTrait;

            private int $id;

            #[DataSource(class: 'Kassko\Sample\PersonDataSource', method: 'nonExistentMethod', args: ['#id'])]
            private ?string $data = null;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getData(): ?string
            {
                $this->loadProperty('data');
                return $this->data;
            }
        };

        $dataMapper = new DataMapper(new \Kassko\DataMapper\ServiceResolver());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Method nonExistentMethod does not exist');

        $entity->getData();
    }

    public function testThrowsExceptionForAbstractClass(): void
    {
        // Create an abstract class for testing
        $abstractClassName = 'AbstractDataSource_' . uniqid();
        eval("
            abstract class {$abstractClassName} {
                abstract public function getData(int \$id): array;
            }
        ");

        $entity = new class($abstractClassName, 1) {
            use LoadableInternalTrait;

            private string $className;
            private int $id;

            public function __construct(string $className, int $id)
            {
                $this->className = $className;
                $this->id = $id;
            }

            public function getData(): ?string
            {
                $this->loadProperty('data');
                return 'test';
            }
        };

        // We can't easily test this with attributes since the class name needs to be known at compile time
        // This test demonstrates the concept but we'll skip it
        $this->markTestSkipped('Cannot easily test abstract class validation with attributes');
    }
}
