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

use Kassko\DataMapper\Attribute\Param;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;

class ParamTest extends TestCase
{
    public function testParamAttributeWithStaticValue(): void
    {
        $param = new Param(value: 'static_value');
        
        $this->assertEquals('static_value', $param->value);
    }

    public function testParamAttributeWithContextExpression(): void
    {
        $param = new Param(value: "expr(context('user_id'))");
        
        $this->assertEquals("expr(context('user_id'))", $param->value);
    }

    public function testParamAttributeWithServiceExpression(): void
    {
        $param = new Param(value: "expr(service('myService'))");
        
        $this->assertEquals("expr(service('myService'))", $param->value);
    }

    public function testParamAttributeWithSourceExpression(): void
    {
        $param = new Param(value: "expr(source('dataSource'))");
        
        $this->assertEquals("expr(source('dataSource'))", $param->value);
    }

    public function testParamAttributeIsReadonly(): void
    {
        $param = new Param(value: 'test');
        
        $reflection = new \ReflectionClass($param);
        $property = $reflection->getProperty('value');
        
        $this->assertTrue($property->isReadOnly());
    }

    public function testParamAttributeTarget(): void
    {
        $reflection = new \ReflectionClass(Param::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        
        $this->assertCount(1, $attributes);
        
        $attrInstance = $attributes[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_PARAMETER, $attrInstance->flags);
    }
}
