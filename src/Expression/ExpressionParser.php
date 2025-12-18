<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Expression;

use ReflectionClass;

class ExpressionParser
{
    private SourceFunctionProvider $sourceFunctionProvider;

    public function __construct(SourceFunctionProvider $sourceFunctionProvider)
    {
        $this->sourceFunctionProvider = $sourceFunctionProvider;
    }

    /**
     * Resolve arguments, replacing property references and expressions with actual values
     *
     * @param array $args
     * @param object $object
     * @param callable $propertyLoader Callback to load a property if needed: function(string $propertyName): void
     * @return array
     */
    public function resolveArgs(array $args, object $object, callable $propertyLoader): array
    {
        $resolved = [];
        
        foreach ($args as $arg) {
            $resolved[] = $this->resolveArg($arg, $object, $propertyLoader);
        }
        
        return $resolved;
    }

    /**
     * Resolve a single argument
     *
     * @param mixed $arg
     * @param object $object
     * @param callable $propertyLoader
     * @return mixed
     */
    private function resolveArg($arg, object $object, callable $propertyLoader)
    {
        if (!is_string($arg)) {
            return $arg;
        }

        // Handle #property syntax
        if (str_starts_with($arg, '#')) {
            return $this->resolvePropertyReference($arg, $object, $propertyLoader);
        }

        // Handle expr(...) syntax
        if (preg_match('/^expr\((.+)\)$/s', $arg, $matches)) {
            return $this->evaluateExpression($matches[1], $object, $propertyLoader);
        }

        // Plain value
        return $arg;
    }

    /**
     * Resolve a property reference like #propertyName
     *
     * @param string $reference
     * @param object $object
     * @param callable $propertyLoader
     * @return mixed
     */
    private function resolvePropertyReference(string $reference, object $object, callable $propertyLoader)
    {
        $propertyName = substr($reference, 1);
        
        // Try to load the property first if it needs loading
        $propertyLoader($propertyName);
        
        $reflectionClass = new ReflectionClass($object);
        
        if ($reflectionClass->hasProperty($propertyName)) {
            $property = $reflectionClass->getProperty($propertyName);
            return $property->getValue($object);
        }
        
        return null;
    }

    /**
     * Evaluate an expression like source('personSource')['car_id']
     *
     * @param string $expression
     * @param object $object
     * @param callable $propertyLoader
     * @return mixed
     */
    private function evaluateExpression(string $expression, object $object, callable $propertyLoader)
    {
        // Parse source('id')['key'] or source('id')
        if (preg_match("/source\('([a-zA-Z0-9_-]+)'\)(?:\['([^']+)'\])?/", $expression, $matches)) {
            $sourceId = $matches[1];
            $key = $matches[2] ?? null;
            
            $result = $this->sourceFunctionProvider->getSourceResult($sourceId);
            
            if ($key !== null && is_array($result)) {
                return $result[$key] ?? null;
            }
            
            return $result;
        }
        
        // If we can't parse it, return the original expression
        return $expression;
    }
}
