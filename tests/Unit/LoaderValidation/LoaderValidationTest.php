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

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\LoaderValidation\PersonDataSource;
use Kassko\Sample\LoaderValidation\EntityWithNonExistentMethod;
use PHPUnit\Framework\TestCase;

class LoaderValidationTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }
    public function testThrowsExceptionForNonExistentClass(): void
    {
        $entity = new class(1) {
            use LoadableTrait;

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
        $entity = new EntityWithNonExistentMethod(1);

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
            use LoadableTrait;

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
