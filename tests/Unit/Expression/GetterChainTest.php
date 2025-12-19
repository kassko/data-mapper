<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit\Expression;

use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use PHPUnit\Framework\TestCase;

class GetterChainTest extends TestCase
{
    private ExpressionParser $parser;
    
    protected function setUp(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $this->parser = new ExpressionParser($sourceFunctionProvider);
    }
    
    public function testGetterMethodIsCalled(): void
    {
        $object = new class {
            private string $name = 'John';
            
            public function getName(): string
            {
                return $this->name;
            }
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#name'], $object, $propertyLoader);
        
        $this->assertSame(['John'], $result);
    }
    
    public function testIsserMethodIsCalledWhenGetterNotAvailable(): void
    {
        $object = new class {
            private bool $active = true;
            
            public function isActive(): bool
            {
                return $this->active;
            }
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#active'], $object, $propertyLoader);
        
        $this->assertSame([true], $result);
    }
    
    public function testHaserMethodIsCalledWhenGetterAndIsserNotAvailable(): void
    {
        $object = new class {
            private bool $permission = true;
            
            public function hasPermission(): bool
            {
                return $this->permission;
            }
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#permission'], $object, $propertyLoader);
        
        $this->assertSame([true], $result);
    }
    
    public function testDirectPropertyAccessWhenNoAccessorMethodAvailable(): void
    {
        $object = new class {
            public string $email = 'test@example.com';
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#email'], $object, $propertyLoader);
        
        $this->assertSame(['test@example.com'], $result);
    }
    
    public function testGetterHasPriorityOverIsser(): void
    {
        $object = new class {
            private string $value = 'from-getter';
            
            public function getValue(): string
            {
                return $this->value;
            }
            
            public function isValue(): string
            {
                return 'from-isser';
            }
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#value'], $object, $propertyLoader);
        
        $this->assertSame(['from-getter'], $result);
    }
    
    public function testIsserHasPriorityOverHaser(): void
    {
        $object = new class {
            private string $flag = 'from-isser';
            
            public function isFlag(): string
            {
                return $this->flag;
            }
            
            public function hasFlag(): string
            {
                return 'from-haser';
            }
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#flag'], $object, $propertyLoader);
        
        $this->assertSame(['from-isser'], $result);
    }
    
    public function testHaserHasPriorityOverDirectAccess(): void
    {
        $object = new class {
            public string $data = 'direct-value';
            
            public function hasData(): string
            {
                return 'from-haser';
            }
        };
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#data'], $object, $propertyLoader);
        
        $this->assertSame(['from-haser'], $result);
    }
    
    public function testReturnsNullWhenPropertyDoesNotExist(): void
    {
        $object = new class {};
        
        $propertyLoader = function() {};
        $result = $this->parser->resolveArgs(['#nonExistent'], $object, $propertyLoader);
        
        $this->assertSame([null], $result);
    }
}
