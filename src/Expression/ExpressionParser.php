<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Expression;

use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\ServiceResolver;
use ReflectionClass;

class ExpressionParser
{
    private SourceFunctionProvider $sourceFunctionProvider;
    private ?ServiceResolver $serviceResolver;
    private ?object $currentObject = null;
    private array $rawData = [];

    public function __construct(
        SourceFunctionProvider $sourceFunctionProvider,
        ?ServiceResolver $serviceResolver = null
    ) {
        $this->sourceFunctionProvider = $sourceFunctionProvider;
        $this->serviceResolver = $serviceResolver;
    }

    /**
     * Set the raw data context for expression evaluation
     *
     * @param array $rawData
     */
    public function setRawData(array $rawData): void
    {
        $this->rawData = $rawData;
    }

    /**
     * Set the current object being hydrated
     *
     * @param object $object
     */
    public function setCurrentObject(object $object): void
    {
        $this->currentObject = $object;
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
     * Get parent object for a given object
     *
     * @param object $object
     * @return object|null
     */
    private function getParentObject(object $object): ?object
    {
        // Get Loader from registry to access parent tracking
        $loader = \Kassko\DataMapper\Registry\LoaderRegistry::get();
        
        if ($loader === null || !method_exists($loader, 'getParentObject')) {
            return null;
        }
        
        return $loader->getParentObject($object);
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

        // Handle ##object syntax (return current object)
        if ($arg === '##object') {
            return $object;
        }
        
        // Handle #parentObject or ##parentObject syntax (return parent object)
        if ($arg === '#parentObject' || $arg === '##parentObject') {
            return $this->getParentObject($object);
        }

        // Handle !#property syntax (bypass getter - direct access)
        if (str_starts_with($arg, '!#')) {
            $propertyName = substr($arg, 2);
            return $this->getPropertyValueDirect($object, $propertyName);
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
        $ucPropertyName = ucfirst($propertyName);
        
        // 1. Try getter: getPropertyName()
        $getterName = 'get' . $ucPropertyName;
        if ($reflectionClass->hasMethod($getterName)) {
            return $object->$getterName();
        }
        
        // 2. Try isser: isPropertyName()
        $isserName = 'is' . $ucPropertyName;
        if ($reflectionClass->hasMethod($isserName)) {
            return $object->$isserName();
        }
        
        // 3. Try haser: hasPropertyName()
        $haserName = 'has' . $ucPropertyName;
        if ($reflectionClass->hasMethod($haserName)) {
            return $object->$haserName();
        }
        
        // 4. Direct property access
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
        // Parse object() - returns the current object
        if (preg_match("/^object\(\)$/", $expression)) {
            return $object;
        }
        
        // Parse parentObject() - returns the parent object
        if (preg_match("/^parentObject\(\)$/", $expression)) {
            return $this->getParentObject($object);
        }
        
        // Parse rawDataItem('key') - returns raw data value
        if (preg_match("/rawDataItem\('([^']+)'\)/", $expression, $matches)) {
            $key = $matches[1];
            return $this->rawData[$key] ?? null;
        }
        
        // Parse rawDataItemExists('key') - checks if key exists in raw data
        if (preg_match("/rawDataItemExists\('([^']+)'\)/", $expression, $matches)) {
            $key = $matches[1];
            return array_key_exists($key, $this->rawData);
        }
        
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
        
        // Parse service('service_id')
        if (preg_match("/service\('([^']+)'\)/", $expression, $matches)) {
            $serviceId = $matches[1];
            
            if ($this->serviceResolver === null) {
                throw new \RuntimeException('ServiceResolver not available for service() function');
            }
            
            return $this->serviceResolver->resolve($serviceId);
        }
        
        // Parse envVar('KEY') - new preferred name
        if (preg_match("/envVar\('([^']+)'\)/", $expression, $matches)) {
            $key = $matches[1];
            
            // Try $_ENV first, then getenv()
            if (isset($_ENV[$key])) {
                return $_ENV[$key];
            }
            
            $envValue = getenv($key);
            return $envValue !== false ? $envValue : null;
        }
        
        // Parse env_var('KEY') - backward compatibility
        if (preg_match("/env_var\('([^']+)'\)/", $expression, $matches)) {
            $key = $matches[1];
            
            // Try $_ENV first, then getenv()
            if (isset($_ENV[$key])) {
                return $_ENV[$key];
            }
            
            $envValue = getenv($key);
            return $envValue !== false ? $envValue : null;
        }
        
        // Parse strictProperty('propertyName')
        if (preg_match("/strictProperty\('([^']+)'\)/", $expression, $matches)) {
            $propertyName = $matches[1];
            return $this->getPropertyValueDirect($this->currentObject, $propertyName);
        }
        
        // Parse contextKeyExists('key') - check if context key exists
        if (preg_match("/contextKeyExists\('([^']+)'\)/", $expression, $matches)) {
            $key = $matches[1];
            return ContextRegistry::has($key);
        }
        
        // Parse context('key') - returns null and logs warning if key doesn't exist
        if (preg_match("/context\('([^']+)'\)/", $expression, $matches)) {
            $key = $matches[1];
            return ContextRegistry::get($key);
        }
        
        // If we can't parse it, return the original expression
        return $expression;
    }

    /**
     * Get property value directly (bypass getter)
     *
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    private function getPropertyValueDirect(object $object, string $propertyName)
    {
        $reflectionClass = new ReflectionClass($object);
        
        if ($reflectionClass->hasProperty($propertyName)) {
            $property = $reflectionClass->getProperty($propertyName);
            return $property->getValue($object);
        }
        
        return null;
    }
}
