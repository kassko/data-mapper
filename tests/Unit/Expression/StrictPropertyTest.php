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

namespace Kassko\DataMapper\Tests\Unit\Expression;

use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use PHPUnit\Framework\TestCase;

class StrictPropertyTest extends TestCase
{
    public function testDirectPropertyAccessWithBangHash(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $object = new class {
            private string $secret = 'direct-value';
            
            public function getSecret(): string
            {
                return 'getter-value';
            }
        };
        
        // Normal #property should use getter
        $normalResult = $parser->resolveArgs(['#secret'], $object, fn() => null);
        $this->assertEquals(['getter-value'], $normalResult);
        
        // !#property should bypass getter
        $directResult = $parser->resolveArgs(['!#secret'], $object, fn() => null);
        $this->assertEquals(['direct-value'], $directResult);
    }
    
    public function testStrictPropertyFunctionInExpression(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $object = new class {
            private string $id = 'original-id';
            
            public function getId(): string
            {
                return 'modified-id';
            }
        };
        
        $parser->setCurrentObject($object);
        
        // strictProperty() should bypass getter
        $result = $parser->resolveArgs(["expr(strictProperty('id'))"], $object, fn() => null);
        $this->assertEquals(['original-id'], $result);
    }
    
    public function testEnvVarRenamed(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $object = new \stdClass();
        
        // Set a test environment variable
        $_ENV['TEST_VAR'] = 'test-value';
        
        // envVar() should work
        $result = $parser->resolveArgs(["expr(envVar('TEST_VAR'))"], $object, fn() => null);
        $this->assertEquals(['test-value'], $result);
        
        // envVarExists() should return true for existing variable
        $result2 = $parser->resolveArgs(["expr(envVarExists('TEST_VAR'))"], $object, fn() => null);
        $this->assertEquals([true], $result2);
        
        // envVarExists() should return false for non-existing variable
        $result3 = $parser->resolveArgs(["expr(envVarExists('NON_EXISTENT_VAR'))"], $object, fn() => null);
        $this->assertEquals([false], $result3);
        
        unset($_ENV['TEST_VAR']);
    }
}
