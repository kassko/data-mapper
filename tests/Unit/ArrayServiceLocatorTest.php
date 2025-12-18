<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit;

use Kassko\DataMapper\ArrayServiceLocator;
use PHPUnit\Framework\TestCase;

final class ArrayServiceLocatorTest extends TestCase
{
    public function testHasReturnsTrueForExistingKey(): void
    {
        $locator = new ArrayServiceLocator([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertTrue($locator->has('key1'));
        $this->assertTrue($locator->has('key2'));
    }

    public function testHasReturnsFalseForNonExistingKey(): void
    {
        $locator = new ArrayServiceLocator([
            'key1' => 'value1',
        ]);

        $this->assertFalse($locator->has('key2'));
        $this->assertFalse($locator->has('nonexistent'));
    }

    public function testGetReturnsValueForExistingKey(): void
    {
        $locator = new ArrayServiceLocator([
            'PersonDataSource' => '@person.data_source',
            'CarRepository' => '@car.repository',
        ]);

        $this->assertEquals('@person.data_source', $locator->get('PersonDataSource'));
        $this->assertEquals('@car.repository', $locator->get('CarRepository'));
    }

    public function testGetThrowsExceptionForNonExistingKey(): void
    {
        $locator = new ArrayServiceLocator([
            'key1' => 'value1',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "nonexistent" not found in locator');

        $locator->get('nonexistent');
    }

    public function testEmptyLocator(): void
    {
        $locator = new ArrayServiceLocator([]);

        $this->assertFalse($locator->has('anything'));
    }

    public function testLocatorWithServiceIds(): void
    {
        $locator = new ArrayServiceLocator([
            'Kassko\Sample\PersonDataSource' => '@person.data_source',
            'Kassko\Sample\PetDataSource' => '@pet.data_source',
        ]);

        $this->assertTrue($locator->has('Kassko\Sample\PersonDataSource'));
        $this->assertEquals('@person.data_source', $locator->get('Kassko\Sample\PersonDataSource'));
    }

    public function testLocatorWithClassNames(): void
    {
        $locator = new ArrayServiceLocator([
            'DIPLOMA' => 'Kassko\Sample\DiplomaProof',
            'CERTIFICATION' => 'Kassko\Sample\CertificationProof',
        ]);

        $this->assertTrue($locator->has('DIPLOMA'));
        $this->assertEquals('Kassko\Sample\DiplomaProof', $locator->get('DIPLOMA'));
    }
}
