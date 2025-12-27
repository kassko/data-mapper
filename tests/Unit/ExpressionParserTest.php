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

use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use PHPUnit\Framework\TestCase;

class ExpressionParserTest extends TestCase
{
    public function testThisArgument(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $object = new \stdClass();
        $object->id = 123;
        
        $result = $parser->resolveArgs(['##object'], $object, fn() => null);
        
        $this->assertSame($object, $result[0]);
    }

    public function testSelfExpression(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $object = new \stdClass();
        $object->name = 'Test';
        
        $result = $parser->resolveArgs(['expr(object())'], $object, fn() => null);
        
        $this->assertSame($object, $result[0]);
    }

    public function testRawDataItemFunction(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $parser->setRawData(['first_name' => 'John', 'last_name' => 'Doe']);
        
        $object = new \stdClass();
        $result = $parser->resolveArgs(["expr(rawDataItem('first_name'))"], $object, fn() => null);
        
        $this->assertEquals('John', $result[0]);
    }

    public function testRawDataItemExistsFunction(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $parser->setRawData(['first_name' => 'John', 'last_name' => 'Doe']);
        
        $object = new \stdClass();
        
        $result1 = $parser->resolveArgs(["expr(rawDataItemExists('first_name'))"], $object, fn() => null);
        $this->assertTrue($result1[0]);
        
        $result2 = $parser->resolveArgs(["expr(rawDataItemExists('middle_name'))"], $object, fn() => null);
        $this->assertFalse($result2[0]);
    }

    public function testRawDataItemWithMissingKey(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $parser->setRawData(['first_name' => 'John']);
        
        $object = new \stdClass();
        $result = $parser->resolveArgs(["expr(rawDataItem('missing_key'))"], $object, fn() => null);
        
        $this->assertNull($result[0]);
    }
}
