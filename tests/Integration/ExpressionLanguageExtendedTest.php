<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\ArrayServiceLocator;
use PHPUnit\Framework\TestCase;

class ExpressionLanguageExtendedTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear context before each test
        ContextRegistry::clear();
    }

    protected function tearDown(): void
    {
        // Clear context after each test
        ContextRegistry::clear();
        
        // Clear environment variables set during tests
        if (isset($_ENV['TEST_VAR'])) {
            unset($_ENV['TEST_VAR']);
        }
    }

    public function testServiceFunction(): void
    {
        $testService = new \stdClass();
        $testService->name = 'TestService';
        
        $serviceLocator = new ArrayServiceLocator([
            'test_service' => $testService,
        ]);
        $serviceResolver = new ServiceResolver(null, [$serviceLocator]);
        
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => []);
        $parser = new ExpressionParser($sourceFunctionProvider, $serviceResolver);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = ["expr(service('test_service'))"];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(1, $resolved);
        $this->assertInstanceOf(\stdClass::class, $resolved[0]);
        $this->assertEquals('TestService', $resolved[0]->name);
    }

    public function testEnvVarFunction(): void
    {
        $_ENV['TEST_VAR'] = 'test_value';
        
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => []);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = ["expr(env_var('TEST_VAR'))"];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(1, $resolved);
        $this->assertEquals('test_value', $resolved[0]);
    }

    public function testEnvVarFunctionReturnsNullForNonExistent(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => []);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = ["expr(env_var('NON_EXISTENT_VAR'))"];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(1, $resolved);
        $this->assertNull($resolved[0]);
    }

    public function testContextFunction(): void
    {
        ContextRegistry::set('shop_quality', 'premium');
        
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => []);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = ["expr(context('shop_quality'))"];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(1, $resolved);
        $this->assertEquals('premium', $resolved[0]);
    }

    public function testContextFunctionReturnsNullForNonExistent(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => []);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = ["expr(context('non_existent'))"];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(1, $resolved);
        $this->assertNull($resolved[0]);
    }

    public function testSourceFunctionStillWorksWithArrayAccess(): void
    {
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => [
            'car_id' => 123,
            'name' => 'John',
        ]);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = ["expr(source('personSource')['car_id'])"];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(1, $resolved);
        $this->assertEquals(123, $resolved[0]);
    }

    public function testMultipleExpressionFunctions(): void
    {
        $_ENV['TEST_VAR'] = 'env_value';
        ContextRegistry::set('ctx_key', 'ctx_value');
        
        $sourceFunctionProvider = new SourceFunctionProvider(fn($id) => ['data' => 'source_value']);
        $parser = new ExpressionParser($sourceFunctionProvider);
        
        $testObject = new class {
            private ?string $value = null;
        };
        
        $args = [
            "expr(env_var('TEST_VAR'))",
            "expr(context('ctx_key'))",
            "expr(source('test')['data'])",
        ];
        $resolved = $parser->resolveArgs($args, $testObject, fn($prop) => null);
        
        $this->assertCount(3, $resolved);
        $this->assertEquals('env_value', $resolved[0]);
        $this->assertEquals('ctx_value', $resolved[1]);
        $this->assertEquals('source_value', $resolved[2]);
    }
}
