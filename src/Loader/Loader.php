<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Loader;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\CustomHydrator;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertyHydratingHook;
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\DataCollector\AttributeCascadeCollector;
use Kassko\DataMapper\DataCollector\DataLineageCollector;
use Kassko\DataMapper\Expression\ExpressionParser;
use Kassko\DataMapper\Expression\SourceFunctionProvider;
use Kassko\DataMapper\Metadata\AttributeReader;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LockedPropertyRegistry;
use Kassko\DataMapper\ServiceResolver;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionProperty;
use WeakMap;

class Loader implements LoaderInterface
{
    /** @var WeakMap<object, array<string, bool>> */
    private WeakMap $loadedProperties;
    
    /** @var WeakMap<object, SourceFunctionProvider> */
    private WeakMap $sourceFunctionProviders;
    
    /** @var WeakMap<object, object> Track parent-child relationships */
    private WeakMap $parentRegistry;
    
    /** @var WeakMap<object, array<string, array{source: string, priority: int}>> Track property => source info for priority handling */
    private WeakMap $propertySourceRegistry;
    
    /** @var WeakMap<object, bool> Track objects that have had hydrating hooks called */
    private WeakMap $hydratingHooksExecuted;
    
    private AttributeReader $attributeReader;
    private ServiceResolver $serviceResolver;
    private LoggerInterface $logger;
    private ?DataLineageCollector $lineageCollector;
    private ?AttributeCascadeCollector $cascadeCollector;
    
    /** @var array<string, callable> */
    private array $customHydrators;

    /**
     * @param ServiceResolver $serviceResolver
     * @param LoggerInterface|null $logger
     * @param array<string, callable> $customHydrators
     * @param DataLineageCollector|null $lineageCollector
     * @param AttributeCascadeCollector|null $cascadeCollector
     */
    public function __construct(
        ServiceResolver $serviceResolver,
        ?LoggerInterface $logger = null,
        array $customHydrators = [],
        ?DataLineageCollector $lineageCollector = null,
        ?AttributeCascadeCollector $cascadeCollector = null
    ) {
        $this->loadedProperties = new WeakMap();
        $this->sourceFunctionProviders = new WeakMap();
        $this->parentRegistry = new WeakMap();
        $this->propertySourceRegistry = new WeakMap();
        $this->hydratingHooksExecuted = new WeakMap();
        $this->logger = $logger ?? new NullLogger();
        $this->customHydrators = $customHydrators;
        $this->serviceResolver = $serviceResolver;
        $this->lineageCollector = $lineageCollector;
        $this->cascadeCollector = $cascadeCollector ?? new AttributeCascadeCollector($this->logger);
        $this->attributeReader = new AttributeReader($this->cascadeCollector);
    }

    public function getServiceResolver(): ServiceResolver
    {
        return $this->serviceResolver;
    }

    /**
     * Get the attribute cascade collector.
     */
    public function getCascadeCollector(): ?AttributeCascadeCollector
    {
        return $this->cascadeCollector;
    }

    /**
     * Check if a property is locked via reflection (to access protected method)
     *
     * @param object $object
     * @param string $propertyName
     * @return bool
     */
    private function checkPropertyLockedViaReflection(object $object, string $propertyName): bool
    {
        if (!method_exists($object, 'isPropertyLocked')) {
            return false;
        }
        
        try {
            $reflectionMethod = new \ReflectionMethod($object, 'isPropertyLocked');
            return (bool) $reflectionMethod->invoke($object, $propertyName);
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * Instantiate an object with constructor parameters resolved via Param attributes.
     * 
     * @param string $className The class to instantiate
     * @return object The instantiated object
     * @throws \InvalidArgumentException If constructor has parameters without Param attribute
     *                                   or if forbidden expressions are used
     */
    public function instantiateWithParams(string $className): object
    {
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        
        // No constructor or no parameters - simple instantiation
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $className();
        }
        
        $parameters = $constructor->getParameters();
        $resolvedArgs = [];
        
        foreach ($parameters as $param) {
            $paramAttrs = $param->getAttributes(Param::class);
            
            if (empty($paramAttrs)) {
                throw new \InvalidArgumentException(sprintf(
                    'Constructor parameter "%s" in class "%s" must have a #[Param] attribute. ' .
                    'DataMapper does not support constructor parameters without Param attributes.',
                    $param->getName(),
                    $className
                ));
            }
            
            /** @var Param $paramAttr */
            $paramAttr = $paramAttrs[0]->newInstance();
            $value = $paramAttr->value;
            
            // Validate: property references are forbidden in constructor
            $this->validateNoPropertyReferences($value, $param->getName(), $className, 'constructor');
            
            // Resolve the expression value
            $resolvedArgs[] = $this->resolveParamValue($value);
        }
        
        return $reflectionClass->newInstanceArgs($resolvedArgs);
    }

    /**
     * Validate that a Param value does not contain property references (for constructor)
     * 
     * @param string $value The Param value
     * @param string $paramName The parameter name
     * @param string $className The class name
     * @param string $context Context description for error message
     * @throws \InvalidArgumentException If property references are found
     */
    private function validateNoPropertyReferences(string $value, string $paramName, string $className, string $context): void
    {
        // Check for simple property references: #id, #name, ##object, etc.
        if (preg_match('/^#\w+|##\w+/', $value)) {
            throw new \InvalidArgumentException(sprintf(
                'Param value for %s parameter "%s" in class "%s" contains forbidden property reference "%s". ' .
                'Constructor parameters cannot reference object properties.',
                $context,
                $paramName,
                $className,
                $value
            ));
        }
        
        // Check for property() expressions inside expr()
        if (preg_match('/property\s*\(/', $value)) {
            throw new \InvalidArgumentException(sprintf(
                'Param value for %s parameter "%s" in class "%s" contains forbidden property() expression. ' .
                'Constructor parameters cannot reference object properties.',
                $context,
                $paramName,
                $className
            ));
        }
    }

    /**
     * Resolve a Param value (static or expression)
     * 
     * @param string $value The Param value
     * @return mixed The resolved value
     */
    private function resolveParamValue(string $value): mixed
    {
        // Check if it's an expression
        if (preg_match('/^expr\((.+)\)$/s', $value, $matches)) {
            $expression = $matches[1];
            
            // Create a minimal expression parser for context/service resolution
            $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
            $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
            
            // Handle context() expression
            if (preg_match("/^context\('([^']+)'\)$/", $expression, $contextMatches)) {
                return \Kassko\DataMapper\Registry\ContextRegistry::get($contextMatches[1]);
            }
            
            // Handle service() expression  
            if (preg_match("/^service\('([^']+)'\)$/", $expression, $serviceMatches)) {
                return $this->serviceResolver->resolve($serviceMatches[1]);
            }
            
            // Handle source() expression (less common in constructors but supported)
            if (preg_match("/^source\('([^']+)'\)$/", $expression, $sourceMatches)) {
                // Can't execute source without an object context
                return null;
            }
            
            // For more complex expressions, return as-is or evaluate
            return $value;
        }
        
        // Static value
        return $value;
    }

    /**
     * Resolve Param-annotated parameters for a method call
     * 
     * @param object $object The object context
     * @param \ReflectionMethod $method The method to resolve parameters for
     * @param bool $isSetter Whether this is a setter (first param must not have Param)
     * @param mixed|null $setterValue The value for the first setter parameter
     * @return array The resolved parameter values
     * @throws \InvalidArgumentException If parameter rules are violated
     */
    public function resolveMethodParams(object $object, \ReflectionMethod $method, bool $isSetter = false, mixed $setterValue = null): array
    {
        $parameters = $method->getParameters();
        $resolvedArgs = [];
        
        foreach ($parameters as $index => $param) {
            $paramAttrs = $param->getAttributes(Param::class);
            $hasParamAttr = !empty($paramAttrs);
            
            if ($isSetter && $index === 0) {
                // First setter parameter must NOT have Param attribute
                if ($hasParamAttr) {
                    throw new \InvalidArgumentException(sprintf(
                        'First parameter "%s" of setter "%s" in class "%s" must not have a #[Param] attribute. ' .
                        'Only additional parameters (from 2nd) can have Param.',
                        $param->getName(),
                        $method->getName(),
                        $method->getDeclaringClass()->getName()
                    ));
                }
                $resolvedArgs[] = $setterValue;
                continue;
            }
            
            // For getters or setter's additional params, Param is required if method has params
            if (!$hasParamAttr) {
                if ($param->isOptional()) {
                    $resolvedArgs[] = $param->getDefaultValue();
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'Parameter "%s" of method "%s" in class "%s" must have a #[Param] attribute.',
                        $param->getName(),
                        $method->getName(),
                        $method->getDeclaringClass()->getName()
                    ));
                }
                continue;
            }
            
            /** @var Param $paramAttr */
            $paramAttr = $paramAttrs[0]->newInstance();
            $value = $paramAttr->value;
            
            // Resolve the expression value with object context
            $resolvedArgs[] = $this->resolveParamValueWithContext($value, $object);
        }
        
        return $resolvedArgs;
    }

    /**
     * Resolve a Param value with object context (for getters/setters)
     * 
     * @param string $value The Param value
     * @param object $object The object context
     * @return mixed The resolved value
     */
    private function resolveParamValueWithContext(string $value, object $object): mixed
    {
        // Check if it's an expression
        if (preg_match('/^expr\((.+)\)$/s', $value, $matches)) {
            $expression = $matches[1];
            
            // Create expression parser with object context
            $sourceFunctionProvider = $this->sourceFunctionProviders[$object] ?? new SourceFunctionProvider(
                fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
            );
            $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
            $expressionParser->setCurrentObject($object);
            
            // Handle context() expression
            if (preg_match("/^context\('([^']+)'\)$/", $expression, $contextMatches)) {
                return \Kassko\DataMapper\Registry\ContextRegistry::get($contextMatches[1]);
            }
            
            // Handle service() expression  
            if (preg_match("/^service\('([^']+)'\)$/", $expression, $serviceMatches)) {
                return $this->serviceResolver->resolve($serviceMatches[1]);
            }
            
            // Handle source() expression
            if (preg_match("/^source\('([^']+)'\)$/", $expression, $sourceMatches)) {
                return $sourceFunctionProvider->executeSource($sourceMatches[1]);
            }
            
            // Handle property() expression
            if (preg_match("/^property\('([^']+)'\)$/", $expression, $propMatches)) {
                $this->loadProperty($object, $propMatches[1]);
                return $this->getPropertyValue($object, $propMatches[1]);
            }
            
            // For more complex expressions, try resolving via expression parser
            $propertyLoader = fn(string $propName) => $this->loadProperty($object, $propName);
            return $expressionParser->resolveArgs([$value], $object, $propertyLoader)[0];
        }
        
        // Handle simple property references
        if (preg_match('/^#(\w+)$/', $value, $matches)) {
            $propName = $matches[1];
            $this->loadProperty($object, $propName);
            return $this->getPropertyValue($object, $propName);
        }
        
        if ($value === '##object') {
            return $object;
        }
        
        // Static value
        return $value;
    }

    /**
     * Get a property value from an object using reflection
     * 
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    private function getPropertyValue(object $object, string $propertyName): mixed
    {
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return null;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        return $property->getValue($object);
    }

    /**
     * Get the data lineage collector.
     */
    public function getLineageCollector(): ?DataLineageCollector
    {
        return $this->lineageCollector;
    }

    /**
     * Check if a property can be hydrated based on source priority
     * 
     * @param object $object
     * @param string $propertyName
     * @param int $incomingPriority
     * @param string $sourceSignature Unique identifier for the source
     * @return bool True if property can be hydrated, false if blocked by higher priority
     */
    private function canHydrateProperty(object $object, string $propertyName, int $incomingPriority, string $sourceSignature): bool
    {
        if (!isset($this->propertySourceRegistry[$object])) {
            return true; // No prior hydration, allow it
        }
        
        $propertyInfo = $this->propertySourceRegistry[$object][$propertyName] ?? null;
        
        if ($propertyInfo === null) {
            return true; // Property not yet hydrated
        }
        
        // If incoming priority is higher, allow overwrite
        if ($incomingPriority > $propertyInfo['priority']) {
            return true;
        }
        
        // If same priority or lower, block
        return false;
    }

    /**
     * Register that a property was hydrated by a specific source
     * 
     * @param object $object
     * @param string $propertyName
     * @param int $priority
     * @param string $sourceSignature
     */
    private function registerPropertyHydration(object $object, string $propertyName, int $priority, string $sourceSignature): void
    {
        if (!isset($this->propertySourceRegistry[$object])) {
            $this->propertySourceRegistry[$object] = [];
        }
        
        $this->propertySourceRegistry[$object][$propertyName] = [
            'source' => $sourceSignature,
            'priority' => $priority,
        ];
    }

    /**
     * Create a signature for a DataSource for tracking purposes
     * 
     * @param SinglePropDataSource|DataSource|MultiPropDataSource|DataSourceRef $source
     * @return string
     */
    private function createSourceSignature($source): string
    {
        if ($source instanceof \Kassko\DataMapper\Attribute\DataSourceRef) {
            if ($source->id !== null) {
                return 'ref:' . $source->id . ($source->fallbacks ? ':fallback' : '');
            }
            if ($source->providers !== null) {
                return 'ref:providers:' . implode(',', $source->providers);
            }
        }
        
        $class = $source->class ?? 'default';
        $method = $source->method;
        $id = $source->id ?? '';
        
        return sprintf('%s::%s[%s]', $class, $method, $id);
    }

    /**
     * Get parent object for a given child object
     *
     * @param object $object
     * @return object|null
     */
    public function getParentObject(object $object): ?object
    {
        return $this->parentRegistry[$object] ?? null;
    }

    /**
     * Check if a property should be hydrated based on attributes and expressions.
     * This method evaluates 'when' expressions in SkipProperty, KeepProperty, and Property.keepWhen.
     *
     * @param ReflectionClass $reflectionClass
     * @param ReflectionProperty $property
     * @param object $object
     * @param array $rawData
     * @return bool
     */
    private function shouldHydratePropertyWithExpressions(
        ReflectionClass $reflectionClass,
        ReflectionProperty $property,
        object $object,
        array $rawData
    ): bool {
        $decisionInfo = $this->attributeReader->getHydrationDecisionInfo($reflectionClass, $property);
        
        // Create an expression parser for evaluating 'when' expressions
        $sourceFunctionProvider = $this->sourceFunctionProviders[$object] ?? new SourceFunctionProvider(
            fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
        );
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        $expressionParser->setRawData($rawData);
        $expressionParser->setCurrentObject($object);
        
        $propertyLoader = fn(string $propName) => $this->loadProperty($object, $propName);
        
        // 1. Check SkipProperty with optional 'when' expression
        if ($decisionInfo['hasSkipProperty']) {
            if ($decisionInfo['skipWhen'] !== null) {
                // Evaluate the 'when' expression
                $skipResult = $this->evaluateWhenExpression(
                    $decisionInfo['skipWhen'],
                    $expressionParser,
                    $object,
                    $propertyLoader,
                    'SkipProperty',
                    $property->getName()
                );
                if ($skipResult) {
                    return false; // Skip this property
                }
                // If expression is false, don't skip - continue to other checks
            } else {
                // No 'when' expression means always skip
                return false;
            }
        }
        
        // 2. If class has SkipAllProperties, check Property.keepWhen and KeepProperty.when
        if ($decisionInfo['hasSkipAllProperties']) {
            // Check Property with keepWhen
            if ($decisionInfo['hasProperty']) {
                if ($decisionInfo['propertyKeepWhen'] !== null) {
                    $keepResult = $this->evaluateWhenExpression(
                        $decisionInfo['propertyKeepWhen'],
                        $expressionParser,
                        $object,
                        $propertyLoader,
                        'Property.keepWhen',
                        $property->getName()
                    );
                    if ($keepResult) {
                        return true; // Keep this property
                    }
                    // If expression is false, Property is treated as absent
                } else {
                    // Property without keepWhen is always considered present
                    return true;
                }
            }
            
            // Check KeepProperty with when
            if ($decisionInfo['hasKeepProperty']) {
                if ($decisionInfo['keepWhen'] !== null) {
                    $keepResult = $this->evaluateWhenExpression(
                        $decisionInfo['keepWhen'],
                        $expressionParser,
                        $object,
                        $propertyLoader,
                        'KeepProperty',
                        $property->getName()
                    );
                    if ($keepResult) {
                        return true; // Keep this property
                    }
                    // If expression is false, KeepProperty is treated as absent
                } else {
                    // KeepProperty without 'when' is always considered present
                    return true;
                }
            }
            
            // Neither Property nor KeepProperty effectively present
            return false;
        }
        
        // Default behavior (KeepAllProperties or no class-level attribute): hydrate
        return true;
    }

    /**
     * Evaluate a 'when' expression and log warning if result is not boolean.
     *
     * @param string $expression
     * @param ExpressionParser $expressionParser
     * @param object $object
     * @param callable $propertyLoader
     * @param string $attributeName For logging purposes
     * @param string $propertyName For logging purposes
     * @return bool
     */
    private function evaluateWhenExpression(
        string $expression,
        ExpressionParser $expressionParser,
        object $object,
        callable $propertyLoader,
        string $attributeName,
        string $propertyName
    ): bool {
        $resolved = $expressionParser->resolveArgs([$expression], $object, $propertyLoader);
        $result = $resolved[0] ?? false;
        
        // Check if result is boolean, log warning if not
        if (!is_bool($result)) {
            $originalType = gettype($result);
            $boolResult = (bool) $result;
            
            $this->logger->warning('Non-boolean result in "when" expression, type coercion occurred', [
                'attribute' => $attributeName,
                'property' => $propertyName,
                'class' => get_class($object),
                'expression' => $expression,
                'original_type' => $originalType,
                'original_value' => is_scalar($result) ? $result : gettype($result),
                'coerced_to' => $boolResult ? 'true' : 'false',
            ]);
            
            // Collect via cascade collector if available
            if ($this->cascadeCollector !== null) {
                $this->cascadeCollector->collectTypeCoercion(
                    get_class($object),
                    $propertyName,
                    $attributeName,
                    $expression,
                    $originalType,
                    $boolResult
                );
            }
            
            return $boolResult;
        }
        
        return $result;
    }

    /**
     * Load all eager properties on the given object
     *
     * @param object $object The object to load eager properties for
     */
    public function loadEagerProperties(object $object): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        // Get all properties including from parent classes
        $allProperties = $this->attributeReader->getAllProperties($reflectionClass);
        
        foreach ($allProperties as $property) {
            $loading = $this->attributeReader->readLoading($property);
            
            // Only load if marked as eager
            if ($loading !== null && $loading->type === Loading::TYPE_EAGER) {
                $this->loadProperty($object, $property->getName());
            }
        }
    }

    /**
     * Load a property on the given object
     *
     * @param object $object The object containing the property
     * @param string $propertyName The name of the property to load
     */
    public function loadProperty(object $object, string $propertyName): void
    {
        // Check if property is locked (prevent loading) - use registry first, fallback to method via reflection
        $isLocked = LockedPropertyRegistry::isLocked($object, $propertyName);
        if (!$isLocked) {
            $isLocked = $this->checkPropertyLockedViaReflection($object, $propertyName);
        }
        
        if ($isLocked) {
            // Record decision point for lineage
            $this->lineageCollector?->recordPropertySkipped(
                get_class($object),
                $propertyName,
                'locked',
                ['reason' => 'Property is locked and cannot be modified']
            );
            return; // Skip loading - property is locked
        }
        
        // Initialize loaded properties registry for this object if needed
        if (!isset($this->loadedProperties[$object])) {
            $this->loadedProperties[$object] = [];
        }
        
        // Initialize source function provider for this object if needed
        if (!isset($this->sourceFunctionProviders[$object])) {
            $this->sourceFunctionProviders[$object] = new SourceFunctionProvider(
                fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
            );
        }
        
        // Check if property is already loaded
        if (isset($this->loadedProperties[$object][$propertyName])) {
            return;
        }
        
        // Get the property's DataSource
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Check for Needs attribute - load dependencies first
        $needs = $this->attributeReader->readNeeds($property);
        if ($needs !== null) {
            // Load dependencies first, in order
            foreach ($needs->properties as $dependencyName) {
                $this->loadProperty($object, $dependencyName);
            }
        }
        
        // Check for CustomHydrator attribute
        $customHydrator = $this->attributeReader->readCustomHydrator($property);
        if ($customHydrator !== null) {
            $this->validateCustomHydratorExclusivity($property);
            $this->loadPropertyWithCustomHydrator($object, $property, $customHydrator);
            return;
        }
        
        // Validate DataSource attribute exclusivity
        $this->validateDataSourceExclusivity($property);
        
        // Check for DataSourceRef with candidates, fallbacks, or providers
        $dataSourceRef = $this->attributeReader->readDataSourceRef($property);
        if ($dataSourceRef !== null && $dataSourceRef->candidates !== null) {
            // Handle candidates (discriminator-based source selection)
            $this->loadPropertyWithCandidates($object, $propertyName, $property, $dataSourceRef);
            return;
        } elseif ($dataSourceRef !== null && $dataSourceRef->fallbacks !== null) {
            // Handle id with fallbacks
            $this->loadPropertyWithFallbacks($object, $propertyName, $property, $dataSourceRef);
            return;
        } elseif ($dataSourceRef !== null && $dataSourceRef->providers !== null) {
            // Handle aggregation
            $this->loadPropertyWithProviders($object, $propertyName, $property, $dataSourceRef);
            return;
        }
        
        // Check for SinglePropDataSource/DataSource on property
        $singleSource = $this->attributeReader->readSinglePropDataSource($property);
        if ($singleSource !== null) {
            $this->loadSingleProperty($object, $property, $singleSource);
            return;
        }
        
        // Check for MultiPropDataSource directly on property
        $multiSourceAttrs = $property->getAttributes(MultiPropDataSource::class);
        if (!empty($multiSourceAttrs)) {
            $multiSource = $multiSourceAttrs[0]->newInstance();
            $this->loadMultipleProperties($object, $multiSource);
            return;
        }
        
        // Check for MultiPropDataSource on class (via DataSourceRef)
        if ($dataSourceRef !== null && $dataSourceRef->id !== null) {
            $multiSource = $this->findMultiPropDataSourceForProperty($object, $dataSourceRef->id);
            if ($multiSource !== null) {
                $this->loadMultipleProperties($object, $multiSource);
                return;
            }
            
            // Check for SinglePropDataSource/DataSource in DataSourcesStore
            $dataSource = $this->resolveDataSourceForProperty($property, $object);
            if ($dataSource !== null) {
                $this->loadSingleProperty($object, $property, $dataSource);
                return;
            }
        }
    }

    /**
     * Load a property with fallback pattern (id + optional fallbacks)
     *
     * @param object $object
     * @param string $propertyName
     * @param ReflectionProperty $property
     * @param \Kassko\DataMapper\Attribute\DataSourceRef $dataSourceRef
     */
    private function loadPropertyWithFallbacks(object $object, string $propertyName, ReflectionProperty $property, $dataSourceRef): void
    {
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        $exceptionClass = $dataSourceRef->exceptionOnNoValidFallback;
        
        // Build the chain: start with primary id, then add fallbacks if any
        $sourceChain = [$dataSourceRef->id];
        if ($dataSourceRef->fallbacks !== null) {
            $sourceChain = array_merge($sourceChain, $dataSourceRef->fallbacks);
        }
        
        foreach ($sourceChain as $sourceId) {
            if (!isset($dataSourceMap[$sourceId])) {
                continue;
            }
            
            $dataSource = $dataSourceMap[$sourceId];
            
            // Check priority before attempting to load
            $sourcePriority = $dataSourceRef->priority;
            $sourceSignature = $this->createSourceSignature($dataSourceRef) . ':' . $sourceId;
            
            if (!$this->canHydrateProperty($object, $propertyName, $sourcePriority, $sourceSignature)) {
                $this->logger->info(
                    'Skipping property hydration due to lower or equal priority',
                    ['property' => $propertyName, 'source' => $sourceSignature, 'priority' => $sourcePriority]
                );
                return; // Property already hydrated by higher priority source
            }
            
            try {
                $result = $this->executeDataSource($dataSource, $object);
                
                // Success! Hydrate the property
                if (is_array($result) && !$this->isListArray($result) && count($result) > 0) {
                    $this->hydrateProperty($object, $propertyName, $result);
                } else {
                    $this->hydratePropertyWithValue($object, $propertyName, $result);
                }
                
                // Register the successful hydration
                $this->registerPropertyHydration($object, $propertyName, $sourcePriority, $sourceSignature);
                $this->loadedProperties[$object][$propertyName] = true;
                return; // Successfully loaded, exit chain
            } catch (\Throwable $e) {
                // Check if this is the expected exception type
                if ($exceptionClass !== null && is_a($e, $exceptionClass)) {
                    // Continue to next source in fallback chain
                    continue;
                }
                // If it's a different exception, rethrow it
                throw $e;
            }
        }
        
        // If we get here, all sources in the chain failed
        throw new \Kassko\DataMapper\Exception\NoValidDataSourceException(
            sprintf('No valid DataSource found in fallback chain for property %s', $propertyName)
        );
    }

    /**
     * Load a property with candidates (expression-based source selection)
     *
     * Evaluates each candidate's 'when' expression and uses the first matching source.
     * If no expression matches, uses defaultCandidate.
     * If a candidate defines its own priority, it takes precedence over the base priority.
     *
     * @param object $object
     * @param string $propertyName
     * @param ReflectionProperty $property
     * @param \Kassko\DataMapper\Attribute\DataSourceRef $dataSourceRef
     */
    private function loadPropertyWithCandidates(object $object, string $propertyName, ReflectionProperty $property, $dataSourceRef): void
    {
        $basePriority = $dataSourceRef->priority;
        $candidates = $dataSourceRef->candidates;
        $defaultCandidate = $dataSourceRef->defaultCandidate;
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        
        // Create expression parser for evaluating expressions
        $expressionParser = new ExpressionParser(
            $this->sourceFunctionProviders[$object] ?? new SourceFunctionProvider(
                fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
            ),
            $this->serviceResolver
        );
        $expressionParser->setCurrentObject($object);
        
        // Property loader callback for expression resolution
        $propertyLoader = fn(string $propName) => $this->loadProperty($object, $propName);
        
        // Find the first matching candidate
        $electedCandidate = null;
        foreach ($candidates as $candidate) {
            $when = $candidate['when'];
            
            // Evaluate the 'when' expression
            $result = $expressionParser->resolveArgs([$when], $object, $propertyLoader)[0];
            
            if ($result === true || $result === 'true' || $result === 1 || $result === '1') {
                $electedCandidate = $candidate;
                break;
            }
        }
        
        // Determine effective priority (candidate's priority takes precedence if defined)
        $effectivePriority = $basePriority;
        // If no candidate matched, use defaultCandidate
        if ($electedCandidate === null) {
            $this->logger->info(
                'No candidate matched for property, using defaultCandidate',
                ['property' => $propertyName, 'candidatesCount' => count($candidates), 'defaultCandidateId' => $defaultCandidate['id']]
            );
            $electedCandidate = $defaultCandidate;
        }
        
        // Determine effective priority (candidate's priority takes precedence if defined)
        $effectivePriority = $basePriority;
        if ($electedCandidate !== null && isset($electedCandidate['priority'])) {
            $effectivePriority = (int) $electedCandidate['priority'];
        }
        
        // Record candidate resolution for lineage
        $this->lineageCollector?->recordCandidateResolution(
            get_class($object),
            $propertyName,
            $candidates,
            $electedCandidate,
            $basePriority,
            $effectivePriority
        );
        
        $sourceId = $electedCandidate['id'];
        $sourceSignature = 'candidates:' . $sourceId;
        
        // Check priority before attempting to load
        if (!$this->canHydrateProperty($object, $propertyName, $effectivePriority, $sourceSignature)) {
            $this->logger->info(
                'Skipping property hydration due to lower or equal priority',
                ['property' => $propertyName, 'source' => $sourceSignature, 'priority' => $effectivePriority]
            );
            $this->lineageCollector?->recordPropertySkipped(
                get_class($object),
                $propertyName,
                'priority',
                ['currentPriority' => $effectivePriority, 'source' => $sourceSignature]
            );
            return;
        }
        
        // Resolve the elected source
        if (!isset($dataSourceMap[$sourceId])) {
            throw new \Kassko\DataMapper\Exception\NoValidDataSourceException(
                sprintf('DataSource "%s" not found in DataSourcesStore for property %s', $sourceId, $propertyName)
            );
        }
        
        $dataSource = $dataSourceMap[$sourceId];
        $result = $this->executeDataSource($dataSource, $object);
        
        // Record the data source call for lineage
        $this->lineageCollector?->recordDataSourceCall(
            get_class($object),
            $dataSource->class ?? 'unknown',
            $dataSource->method,
            $dataSource->args,
            $result,
            $sourceId,
            $dataSource->sensitiveKeys
        );
        
        // Hydrate the property
        if (is_array($result) && !$this->isListArray($result) && count($result) > 0) {
            $this->hydrateProperty($object, $propertyName, $result);
        } else {
            $this->hydratePropertyWithValue($object, $propertyName, $result);
        }
        
        // Register the successful hydration
        $this->registerPropertyHydration($object, $propertyName, $effectivePriority, $sourceSignature);
        $this->loadedProperties[$object][$propertyName] = true;
        
        // Record property hydration for lineage
        $this->lineageCollector?->recordPropertyHydration(
            get_class($object),
            $propertyName,
            null,
            $result,
            $sourceSignature,
            $effectivePriority
        );
    }

    /**
     * Load a property with aggregation from multiple providers
     *
     * @param object $object
     * @param string $propertyName
     * @param ReflectionProperty $property
     * @param \Kassko\DataMapper\Attribute\DataSourceRef $dataSourceRef
     */
    private function loadPropertyWithProviders(object $object, string $propertyName, ReflectionProperty $property, $dataSourceRef): void
    {
        $sourcePriority = $dataSourceRef->priority;
        $sourceSignature = $this->createSourceSignature($dataSourceRef);
        
        // Check priority before attempting to load
        if (!$this->canHydrateProperty($object, $propertyName, $sourcePriority, $sourceSignature)) {
            $this->logger->info(
                'Skipping property hydration due to lower or equal priority',
                ['property' => $propertyName, 'source' => $sourceSignature, 'priority' => $sourcePriority]
            );
            return;
        }
        
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        $aggregatedResult = [];
        $ignoreNotFound = $dataSourceRef->ignoreProviderOnNotFound;

        foreach ($dataSourceRef->providers as $sourceId) {
            if (!isset($dataSourceMap[$sourceId])) {
                if ($ignoreNotFound) {
                    $this->logger->info(
                        'Skipping unknown provider (ignoreProviderOnNotFound is enabled)',
                        ['property' => $propertyName, 'provider' => $sourceId]
                    );
                    continue;
                }
                // Default behavior: skip silently (existing behavior)
                continue;
            }
            
            $dataSource = $dataSourceMap[$sourceId];
            
            try {
                $result = $this->executeDataSource($dataSource, $object);
            } catch (\Throwable $e) {
                if ($ignoreNotFound) {
                    $this->logger->info(
                        'Skipping failed provider (ignoreProviderOnNotFound is enabled)',
                        ['property' => $propertyName, 'provider' => $sourceId, 'error' => $e->getMessage()]
                    );
                    continue;
                }
                throw $e;
            }
            
            // Only aggregate arrays
            if (is_array($result)) {
                $aggregatedResult = array_replace_recursive($aggregatedResult, $result);
            }
        }
        
        // Hydrate the property with aggregated result
        if (!empty($aggregatedResult)) {
            if (!$this->isListArray($aggregatedResult)) {
                $this->hydrateProperty($object, $propertyName, $aggregatedResult);
            } else {
                $this->hydratePropertyWithValue($object, $propertyName, $aggregatedResult);
            }
        }
        
        $this->registerPropertyHydration($object, $propertyName, $sourcePriority, $sourceSignature);
        $this->loadedProperties[$object][$propertyName] = true;
    }

    /**
     * Load a single property with SinglePropDataSource or DataSource
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param SinglePropDataSource|DataSource $source
     */
    private function loadSingleProperty(
        object $object,
        ReflectionProperty $property,
        SinglePropDataSource|DataSource $source
    ): void {
        $propertyName = $property->getName();
        $sourcePriority = $source->priority;
        $sourceSignature = $this->createSourceSignature($source);
        
        // Check priority before attempting to load
        if (!$this->canHydrateProperty($object, $propertyName, $sourcePriority, $sourceSignature)) {
            $this->logger->info(
                'Skipping property hydration due to lower or equal priority',
                ['property' => $propertyName, 'source' => $sourceSignature, 'priority' => $sourcePriority]
            );
            
            // Record decision point for lineage
            $this->lineageCollector?->recordPropertySkipped(
                get_class($object),
                $propertyName,
                'priority',
                ['currentPriority' => $sourcePriority, 'source' => $sourceSignature]
            );
            return;
        }
        
        $data = $this->callDataSource($source, $object);
        
        // Record the data source call for lineage
        $this->lineageCollector?->recordDataSourceCall(
            get_class($object),
            $source->class ?? 'unknown',
            $source->method,
            $source->args,
            $data,
            $source->id ?? null,
            $source->sensitiveKeys
        );
        
        // Apply recursive hydration if needed (handles PropertyCandidates, nested objects, etc.)
        $originalData = $data;
        $data = $this->applyRecursiveHydration($property, $data, 0, $object);
        
        $this->setPropertyValue($object, $property, $data);
        $this->registerPropertyHydration($object, $propertyName, $sourcePriority, $sourceSignature);
        $this->loadedProperties[$object][$propertyName] = true;
        $this->handleContextAttribute($property, $data, $object);
        
        // Record property hydration for lineage
        $this->lineageCollector?->recordPropertyHydration(
            get_class($object),
            $propertyName,
            $originalData,
            $data,
            $sourceSignature,
            $sourcePriority
        );
    }

    /**
     * Find MultiPropDataSource for a property by source ID
     *
     * @param object $object
     * @param string $sourceId
     * @return MultiPropDataSource|null
     */
    private function findMultiPropDataSourceForProperty(object $object, string $sourceId): ?MultiPropDataSource
    {
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        $source = $dataSourceMap[$sourceId] ?? null;
        
        if ($source instanceof MultiPropDataSource) {
            return $source;
        }
        
        return null;
    }

    /**
     * Load multiple properties from MultiPropDataSource
     *
     * @param object $object
     * @param MultiPropDataSource $source
     */
    private function loadMultipleProperties(
        object $object,
        MultiPropDataSource $source
    ): void {
        $sourcePriority = $source->priority;
        $sourceSignature = $this->createSourceSignature($source);
        
        $data = $this->callDataSource($source, $object);
        
        // Record the data source call for lineage
        $this->lineageCollector?->recordDataSourceCall(
            get_class($object),
            $source->class ?? 'unknown',
            $source->method,
            $source->args,
            $data,
            $source->id ?? null,
            $source->sensitiveKeys
        );
        
        if (!is_array($data)) {
            return;
        }
        
        // Execute before_hydrate_object hooks (only once per object)
        $hooksAlreadyExecuted = $this->hydratingHooksExecuted[$object] ?? false;
        if (!$hooksAlreadyExecuted) {
            $this->executeHydratingHooks($object, 'before', $data);
        }
        
        // Filter properties based on loadingScope
        $properties = $this->filterProperties($object, $source, $data);
        
        foreach ($properties as $property) {
            $propName = $property->getName();
            
            // Skip if already loaded
            if (isset($this->loadedProperties[$object][$propName])) {
                $this->lineageCollector?->recordPropertySkipped(
                    get_class($object),
                    $propName,
                    'already_loaded',
                    ['source' => $sourceSignature]
                );
                continue;
            }
            
            // Check priority before attempting to load
            if (!$this->canHydrateProperty($object, $propName, $sourcePriority, $sourceSignature)) {
                $this->logger->info(
                    'Skipping property hydration due to lower or equal priority',
                    ['property' => $propName, 'source' => $sourceSignature, 'priority' => $sourcePriority]
                );
                $this->lineageCollector?->recordPropertySkipped(
                    get_class($object),
                    $propName,
                    'priority',
                    ['currentPriority' => $sourcePriority, 'source' => $sourceSignature]
                );
                continue;
            }
            
            // Use hydrateProperty which handles instance mapping
            $this->hydrateProperty($object, $propName, $data);
            $this->registerPropertyHydration($object, $propName, $sourcePriority, $sourceSignature);
            $this->loadedProperties[$object][$propName] = true;
        }
        
        // Execute after_hydrate_object hooks (only once per object)
        if (!$hooksAlreadyExecuted) {
            $this->executeHydratingHooks($object, 'after', $data);
            $this->hydratingHooksExecuted[$object] = true;
        }
    }

    /**
     * Filter properties based on MultiPropDataSource loading scope
     *
     * @param object $object
     * @param MultiPropDataSource $source
     * @param array $data
     * @return array<ReflectionProperty>
     */
    private function filterProperties(
        object $object,
        MultiPropDataSource $source,
        array $data
    ): array {
        $allProperties = $this->getHydratableProperties($object, $source);
        
        return match ($source->loadingScope) {
            MultiPropDataSource::SCOPE_ALL => $allProperties,
            MultiPropDataSource::SCOPE_ONLY_KEYS => array_filter(
                $allProperties,
                fn($p) => in_array($this->getPropertyNameMapping($p), $source->loadingScopeKeys)
            ),
            MultiPropDataSource::SCOPE_EXCEPT_KEYS => array_filter(
                $allProperties,
                fn($p) => !in_array($this->getPropertyNameMapping($p), $source->loadingScopeKeys)
            ),
            MultiPropDataSource::SCOPE_ONLY_PROPS => array_filter(
                $allProperties,
                fn($p) => in_array($p->getName(), $source->loadingScopeProps)
            ),
            MultiPropDataSource::SCOPE_EXCEPT_PROPS => array_filter(
                $allProperties,
                fn($p) => !in_array($p->getName(), $source->loadingScopeProps)
            ),
            default => $allProperties,
        };
    }

    /**
     * Get all hydratable properties that reference this data source
     *
     * @param object $object
     * @param MultiPropDataSource $source
     * @return array<ReflectionProperty>
     */
    private function getHydratableProperties(object $object, MultiPropDataSource $source): array
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = [];

        foreach ($reflectionClass->getProperties() as $property) {
            // Check if property references this data source
            $dataSourceRef = $this->attributeReader->readDataSourceRef($property);
            if ($dataSourceRef !== null && $dataSourceRef->id === $source->id) {
                $properties[] = $property;
            }

            // Check if property has a MultiPropDataSource with the same signature
            $dataSourceRef = $this->attributeReader->readMultiPropDataSource($property);
            if ($dataSourceRef !== null && $dataSourceRef->id === $source->id) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * Call a data source (SinglePropDataSource, DataSource, or MultiPropDataSource)
     *
     * @param SinglePropDataSource|DataSource|MultiPropDataSource $source
     * @param object $object
     * @return mixed
     */
    private function callDataSource(SinglePropDataSource|DataSource|MultiPropDataSource $source, object $object): mixed
    {
        $dataSourceInstance = $this->resolveDataSource($source->class ?? '');
        $resolvedArgs = $this->resolveArgs($source->args, $object);
        
        // Validate method exists before calling
        if (!method_exists($dataSourceInstance, $source->method)) {
            throw new \RuntimeException(
                sprintf(
                    'Method %s does not exist on DataSource class %s',
                    $source->method,
                    get_class($dataSourceInstance)
                )
            );
        }
        
        return call_user_func_array(
            [$dataSourceInstance, $source->method],
            $resolvedArgs
        );
    }


    /**
     * Execute a DataSource and return the result
     *
     * @param DataSource|SinglePropDataSource|MultiPropDataSource $dataSource
     * @param object $object
     * @return mixed
     */
    private function executeDataSource(DataSource|SinglePropDataSource|MultiPropDataSource $dataSource, object $object)
    {
        $dataSourceInstance = $this->resolveDataSource($dataSource->class ?? '');
        $resolvedArgs = $this->resolveArgs($dataSource->args, $object);
        
        // Validate method exists before calling
        if (!method_exists($dataSourceInstance, $dataSource->method)) {
            throw new \RuntimeException(
                sprintf(
                    'Method %s does not exist on DataSource class %s',
                    $dataSource->method,
                    get_class($dataSourceInstance)
                )
            );
        }
        
        return call_user_func_array(
            [$dataSourceInstance, $dataSource->method],
            $resolvedArgs
        );
    }

    /**
     * Resolve the DataSource for a property (either from attribute or from store via ref)
     *
     * @param ReflectionProperty $property
     * @param object $object
     * @return SinglePropDataSource|DataSource|null
     */
    private function resolveDataSourceForProperty(ReflectionProperty $property, object $object): SinglePropDataSource|DataSource|null
    {
        // First check for direct SinglePropDataSource or DataSource attribute
        $dataSource = $this->attributeReader->readSinglePropDataSource($property);
        if ($dataSource !== null) {
            return $dataSource;
        }
        
        // Check for DataSourceRef attribute
        $dataSourceRef = $this->attributeReader->readDataSourceRef($property);
        if ($dataSourceRef !== null && $dataSourceRef->id !== null) {
            $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
            $source = $dataSourceMap[$dataSourceRef->id] ?? null;
            // Only return if it's SinglePropDataSource or DataSource (not MultiPropDataSource)
            if ($source instanceof SinglePropDataSource || $source instanceof DataSource) {
                return $source;
            }
        }
        
        return null;
    }

    /**
     * Resolve a DataSource class (either instantiate or get from container)
     *
     * @param string $class
     * @return object
     */
    private function resolveDataSource(string $class): object
    {
        return $this->serviceResolver->resolve($class);
    }

    /**
     * Resolve arguments, replacing property references and expressions with actual values
     *
     * @param array $args
     * @param object $object
     * @return array
     */
    private function resolveArgs(array $args, object $object): array
    {
        $sourceFunctionProvider = $this->sourceFunctionProviders[$object];
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        
        return $expressionParser->resolveArgs($args, $object, function(string $propertyName) use ($object) {
            $this->loadProperty($object, $propertyName);
        });
    }

    /**
     * Execute a DataSource by its id from the store
     *
     * @param object $object
     * @param string $sourceId
     * @return mixed
     */
    private function executeDataSourceById(object $object, string $sourceId)
    {
        $dataSourceMap = $this->attributeReader->getDataSourceMap($object);
        
        if (!isset($dataSourceMap[$sourceId])) {
            // Sanitize sourceId for error message to prevent log injection
            $sanitizedId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sourceId);
            throw new \RuntimeException(
                sprintf('DataSource with id "%s" not found in DataSourcesStore', $sanitizedId)
            );
        }
        
        $dataSource = $dataSourceMap[$sourceId];
        
        // Handle different data source types
        if ($dataSource instanceof MultiPropDataSource) {
            // Execute the data source
            $result = $this->callDataSource($dataSource, $object);
            
            // Also hydrate all related properties
            if (is_array($result)) {
                $this->loadMultipleProperties($object, $dataSource);
            }
            
            return $result;
        } else {
            // Execute the data source (legacy DataSource)
            $result = $this->executeDataSource($dataSource, $object);
            
            return $result;
        }
    }

    /**
     * Hydrate a property with data from an array
     *
     * @param object $object
     * @param string $propertyName
     * @param array $data
     * @param int $currentDepth Current recursion depth
     */
    private function hydrateProperty(object $object, string $propertyName, array $data, int $currentDepth = 0): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Check if property has instance mapping
        $propertyAttr = $this->attributeReader->readProperty($property);
        if ($propertyAttr !== null && $propertyAttr->mapping !== null) {
            // Extract data using mapping from flat parent data
            $mappedData = $this->applyInstanceMapping($data, $propertyAttr);
            
            // Only proceed if we have mapped data
            if (!empty($mappedData)) {
                $value = $this->applyRecursiveHydration($property, $mappedData, $currentDepth, $object);
                $this->setPropertyValue($object, $property, $value);
                $this->handleContextAttribute($property, $value, $object);
            }
            return;
        }
        
        // Get the field name mapping
        $fieldName = $this->getPropertyNameMapping($property);
        
        if (!array_key_exists($fieldName, $data)) {
            // Log warning when key is missing
            $this->logger->warning('Missing raw data key for property hydration', [
                'class' => $reflectionClass->getName(),
                'property' => $propertyName,
                'expected_key' => $fieldName,
            ]);
            return;
        }
        
        $value = $data[$fieldName];
        
        // Check if we should perform recursive hydration
        $value = $this->applyRecursiveHydration($property, $value, $currentDepth, $object);
        
        // Set property value using setter resolution
        $this->setPropertyValue($object, $property, $value);
        
        // Handle Context attribute
        $this->handleContextAttribute($property, $value, $object);
    }

    /**
     * Hydrate a property with a single value
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @param int $currentDepth Current recursion depth
     */
    private function hydratePropertyWithValue(object $object, string $propertyName, $value, int $currentDepth = 0): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        if (!$reflectionClass->hasProperty($propertyName)) {
            return;
        }
        
        $property = $reflectionClass->getProperty($propertyName);
        
        // Check if we should perform recursive hydration
        $value = $this->applyRecursiveHydration($property, $value, $currentDepth, $object);
        
        // Set property value using setter resolution
        $this->setPropertyValue($object, $property, $value);
        
        // Handle Context attribute
        $this->handleContextAttribute($property, $value, $object);
    }

    /**
     * Get the field/property name mapping from attributes
     *
     * @param ReflectionProperty $property
     * @return string
     */
    private function getPropertyNameMapping(ReflectionProperty $property): string
    {
        // Use Property attribute
        $propertyAttr = $this->attributeReader->readProperty($property);
        if ($propertyAttr !== null && $propertyAttr->name !== null) {
            return $propertyAttr->name;
        }
        
        // Default to property name
        return $property->getName();
    }

    /**
     * Apply recursive hydration if needed
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param int $currentDepth
     * @param object|null $parentObject Parent object for tracking parent-child relationships
     * @return mixed
     */
    private function applyRecursiveHydration(ReflectionProperty $property, mixed $value, int $currentDepth, ?object $parentObject = null): mixed
    {
        // Read the Property attribute
        $propertyAttr = $this->attributeReader->readProperty($property);
        
        // Check if this property has configCandidates (polymorphic hydration)
        $hasConfigCandidates = $propertyAttr !== null && $propertyAttr->configCandidates !== null;
        
        // If no Property attribute, return value as-is
        if ($propertyAttr === null) {
            return $value;
        }
        
        // Check depth limit
        $loading = $this->attributeReader->readLoading($property);
        if ($loading !== null && $loading->depth !== null && $currentDepth >= $loading->depth) {
            return $value;
        }
        
        // If value is not an array, can't hydrate
        if (!is_array($value)) {
            return $value;
        }
        
        // Get PropertyConfigStore for the parent class if we have configCandidates or config reference
        // Use cascaded version to include configs from parent classes and traits
        $configStore = null;
        if ($hasConfigCandidates || $propertyAttr->config !== null) {
            $reflectionClass = new \ReflectionClass($parentObject ?? $property->getDeclaringClass()->getName());
            $configStore = $this->attributeReader->readCascadedPropertyConfigStore($reflectionClass);
        }
        
        // Handle array of objects (collection)
        if ($this->isListArray($value)) {
            $result = [];
            foreach ($value as $itemData) {
                if (!is_array($itemData)) {
                    $result[] = $itemData;
                    continue;
                }
                
                // Resolve property config for each item if configCandidates exists
                $itemPropertyAttr = $propertyAttr;
                if ($hasConfigCandidates && $configStore !== null) {
                    $resolvedConfig = $this->resolvePropertyConfig($propertyAttr, $configStore, $itemData);
                    if ($resolvedConfig !== null) {
                        // Merge config into a new Property-like object
                        $itemPropertyAttr = $this->mergePropertyConfig($propertyAttr, $resolvedConfig);
                    }
                } elseif ($propertyAttr->config !== null && $configStore !== null) {
                    // Single config reference
                    $config = $configStore->configs[$propertyAttr->config] ?? null;
                    if ($config !== null) {
                        $itemPropertyAttr = $this->mergePropertyConfig($propertyAttr, $config);
                    }
                }
                
                // If still no class defined, skip this item
                if ($itemPropertyAttr === null || $itemPropertyAttr->class === null) {
                    $result[] = $itemData;
                    continue;
                }
                
                // Instantiate the nested object with Param attribute support
                $nestedObject = $this->instantiateWithParams($itemPropertyAttr->class);
                
                // Track parent-child relationship
                if ($parentObject !== null) {
                    $this->parentRegistry[$nestedObject] = $parentObject;
                }
                
                // Execute after_instantiating hooks
                $this->executeInstantiatingHooks($nestedObject, $itemData);
                
                // Hydrate the nested list item (using mapped data keys)
                $this->hydrateObject($nestedObject, $itemData, $itemPropertyAttr, $currentDepth + 1);
                
                $result[] = $nestedObject;
            }
            return $result;
        }
        
        // Single object (not a list)
        $effectivePropertyAttr = $propertyAttr;
        
        // Resolve config if using configCandidates
        if ($hasConfigCandidates && $configStore !== null) {
            $resolvedConfig = $this->resolvePropertyConfig($propertyAttr, $configStore, $value);
            if ($resolvedConfig !== null) {
                $effectivePropertyAttr = $this->mergePropertyConfig($propertyAttr, $resolvedConfig);
            }
        } elseif ($propertyAttr->config !== null && $configStore !== null) {
            // Single config reference
            $config = $configStore->configs[$propertyAttr->config] ?? null;
            if ($config !== null) {
                $effectivePropertyAttr = $this->mergePropertyConfig($propertyAttr, $config);
            }
        }
        
        // If no class defined, return as-is
        if ($effectivePropertyAttr === null || $effectivePropertyAttr->class === null) {
            return $value;
        }
        
        // Get the actual class to instantiate
        $className = $effectivePropertyAttr->class;
        
        // Instantiate the nested object with Param attribute support
        $nestedObject = $this->instantiateWithParams($className);
        
        // Track parent-child relationship
        if ($parentObject !== null) {
            $this->parentRegistry[$nestedObject] = $parentObject;
        }
        
        // Execute after_instantiating hooks
        $this->executeInstantiatingHooks($nestedObject, $value);
        
        // Hydrate the single nested object (using mapped data keys)
        $this->hydrateObject($nestedObject, $value, $effectivePropertyAttr, $currentDepth + 1);
        
        return $nestedObject;
    }

    /**
     * Resolve property configuration from configCandidates based on 'when' expression evaluation
     *
     * @param Property $propertyAttr The Property attribute with configCandidates
     * @param PropertyConfigStore $configStore The store containing all configs
     * @param array $rawDataItem Raw data to evaluate expressions against
     * @return PropertyConfig|null Returns null for no-op (empty array defaultConfigCandidate)
     */
    private function resolvePropertyConfig(Property $propertyAttr, PropertyConfigStore $configStore, array $rawDataItem): ?PropertyConfig
    {
        // Create a temporary expression parser for expression evaluation
        $sourceFunctionProvider = new SourceFunctionProvider(fn() => null);
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        $expressionParser->setRawData($rawDataItem);
        
        foreach ($propertyAttr->configCandidates as $candidate) {
            $when = $candidate['when'];
            $configId = $candidate['id'];
            
            // Check if it's an expression
            if (preg_match('/^expr\((.+)\)$/s', $when, $matches)) {
                $expression = $matches[1];
                
                // Parse and evaluate the expression
                // For rawDataItemExists and rawDataItem, we need to evaluate manually
                if (preg_match("/rawDataItemExists\('([^']+)'\)/", $expression, $keyMatches)) {
                    $key = $keyMatches[1];
                    $result = array_key_exists($key, $rawDataItem);
                } elseif (preg_match("/rawDataItem\('([^']+)'\)/", $expression, $keyMatches)) {
                    // Support rawDataItem() expressions
                    $key = $keyMatches[1];
                    $value = $rawDataItem[$key] ?? null;
                    // If followed by comparison, evaluate it
                    if (preg_match("/rawDataItem\('[^']+'\)\s*==\s*'([^']+)'/", $expression, $eqMatches)) {
                        $result = ($value == $eqMatches[1]);
                    } else {
                        $result = (bool)$value;
                    }
                } elseif (preg_match("/contextKeyExists\('([^']+)'\)/", $expression, $keyMatches)) {
                    // Support contextKeyExists() expression
                    $key = $keyMatches[1];
                    $result = ContextRegistry::has($key);
                } elseif (preg_match("/context\('([^']+)'\)/", $expression, $keyMatches)) {
                    $key = $keyMatches[1];
                    $contextValue = ContextRegistry::get($key);
                    // Evaluate the full expression - handle equality comparisons
                    if (preg_match("/context\('([^']+)'\)\s*===\s*'([^']+)'/", $expression, $eqMatches)) {
                        $result = ($contextValue === $eqMatches[2]);
                    } elseif (preg_match("/context\('([^']+)'\)\s*==\s*'([^']+)'/", $expression, $eqMatches)) {
                        $result = ($contextValue == $eqMatches[2]);
                    } else {
                        $result = (bool)$contextValue;
                    }
                } else {
                    // For other expressions, attempt basic evaluation
                    // This is a fallback - complex expressions may need additional handling
                    $result = false;
                }
                
                if ($result === true) {
                    return $configStore->configs[$configId] ?? null;
                }
            }
        }
        
        // No expression matched, use defaultConfigCandidate
        if ($propertyAttr->defaultConfigCandidate !== null) {
            // Handle no-op: empty array means intentionally do nothing
            if (is_array($propertyAttr->defaultConfigCandidate) && empty($propertyAttr->defaultConfigCandidate)) {
                return null;  // No-op: don't apply any config
            }
            // String config ID
            if (is_string($propertyAttr->defaultConfigCandidate)) {
                return $configStore->configs[$propertyAttr->defaultConfigCandidate] ?? null;
            }
        }
        
        return null;
    }

    /**
     * Merge a PropertyConfig into a new Property-like object
     *
     * Property attribute values take precedence over PropertyConfig values.
     * This allows Property to override specific PropertyConfig settings.
     *
     * @param Property $propertyAttr Original Property attribute
     * @param PropertyConfig $config PropertyConfig to merge
     * @return Property
     */
    private function mergePropertyConfig(Property $propertyAttr, PropertyConfig $config): Property
    {
        return new Property(
            // Property.name takes precedence over PropertyConfig.name
            name: $propertyAttr->name ?? $config->name,
            class: $propertyAttr->class ?? $config->class,
            expand: $propertyAttr->expand ?? $config->expand,
            noExpand: $propertyAttr->noExpand ?? $config->noExpand,
            mapping: $propertyAttr->mapping ?? $config->mapping,
        );
    }

    /**
     * Hydrate an object with data
     *
     * @param object $object
     * @param array $data
     * @param Property|null $propertyAttr Property attribute for expand/noExpand control
     * @param int $currentDepth Current recursion depth
     */
    private function hydrateObject(object $object, array $data, ?Property $propertyAttr = null, int $currentDepth = 0): void
    {
        $reflectionClass = new ReflectionClass($object);
        
        // Execute before_hydrate_object hooks
        $this->executeHydratingHooks($object, 'before', $data);
        
        // Get all properties including from parent classes
        $allProperties = $this->attributeReader->getAllProperties($reflectionClass);
        
        // Build list of properties to expand or skip
        $expandList = $propertyAttr !== null && $propertyAttr->expand !== null 
            ? array_map('trim', explode(',', $propertyAttr->expand)) 
            : null;
        $noExpandList = $propertyAttr !== null && $propertyAttr->noExpand !== null 
            ? array_map('trim', explode(',', $propertyAttr->noExpand)) 
            : null;
        
        foreach ($allProperties as $property) {
            $propName = $property->getName();
            
            // Check if we should hydrate this property based on expand/noExpand
            if ($expandList !== null && !in_array($propName, $expandList)) {
                continue;
            }
            if ($noExpandList !== null && in_array($propName, $noExpandList)) {
                continue;
            }
            
            // Check if property should be hydrated based on attributes and expressions
            if (!$this->shouldHydratePropertyWithExpressions($reflectionClass, $property, $object, $data)) {
                continue;
            }
            
            // Get the field name mapping
            $fieldName = $this->getPropertyNameMapping($property);
            
            if (!array_key_exists($fieldName, $data)) {
                // Log warning when key is missing
                $this->logger->warning('Missing raw data key for property hydration', [
                    'class' => $reflectionClass->getName(),
                    'property' => $propName,
                    'expected_key' => $fieldName,
                ]);
                continue;
            }
            
            $value = $data[$fieldName];
            
            // Apply recursive hydration if needed
            $value = $this->applyRecursiveHydration($property, $value, $currentDepth, $object);
            
            // Set property value using setter resolution
            $this->setPropertyValue($object, $property, $value);
            
            // Handle Context attribute
            $this->handleContextAttribute($property, $value, $object);
        }
        
        // Execute after_hydrate_object hooks (after hydration, before property setting hooks)
        $this->executeHydratingHooks($object, 'after', $data);
    }

    /**
     * Set a property value using setter resolution logic
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param mixed $value
     */
    private function setPropertyValue(object $object, ReflectionProperty $property, mixed $value): void
    {
        $propertyName = $property->getName();
        
        // Execute before_set_property hooks
        $this->executeSettingHooks($object, $property, 'before', $value);
        
        // 1. Check for explicit Setter attribute
        $setter = $this->attributeReader->readSetter($property);
        if ($setter !== null && $setter->name !== null) {
            // Use explicit setter method
            if (method_exists($object, $setter->name)) {
                $object->{$setter->name}($value);
                // Execute after_set_property hooks
                $this->executeSettingHooks($object, $property, 'after', $value);
                return;
            }
        }
        
        // 2. If value is a non-associative array (list), look for adder method
        if (is_array($value) && $this->isListArray($value)) {
            $adderMethod = 'add' . ucfirst($propertyName) . 'Item';
            if (method_exists($object, $adderMethod)) {
                foreach ($value as $item) {
                    $object->$adderMethod($item);
                }
                // Execute after_set_property hooks
                $this->executeSettingHooks($object, $property, 'after', $value);
                return;
            }
        }
        
        // 3. Look for setter method
        $setterMethod = 'set' . ucfirst($propertyName);
        if (method_exists($object, $setterMethod)) {
            $object->$setterMethod($value);
            // Execute after_set_property hooks
            $this->executeSettingHooks($object, $property, 'after', $value);
            return;
        }
        
        // 4. Fall back to direct property assignment via reflection
        $property->setValue($object, $value);
        
        // Execute after_set_property hooks
        $this->executeSettingHooks($object, $property, 'after', $value);
    }

    /**
     * Check if an array is a list (non-associative array with sequential numeric keys)
     *
     * @param array $array
     * @return bool
     */
    private function isListArray(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * Handle Context attribute - set context values when property is loaded
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param object|null $object The current object being hydrated (for evaluating method-based context)
     */
    private function handleContextAttribute(ReflectionProperty $property, mixed $value, ?object $object = null): void
    {
        $contexts = $this->attributeReader->readAllContexts($property);
        
        if (empty($contexts)) {
            return;
        }
        
        $objectClass = $property->getDeclaringClass()->getName();
        $propertyName = $property->getName();
        
        // Set all context values from all Context attributes
        foreach ($contexts as $context) {
            $this->processContextEntries($context->entries, $objectClass, $propertyName, $object);
        }
    }

    /**
     * Process context entries and set them in the ContextRegistry.
     * 
     * @param array $entries The context entries to process
     * @param string $objectClass The class name for lineage tracking
     * @param string $propertyName The property name for lineage tracking
     * @param object|null $object The current object for method-based context
     */
    private function processContextEntries(array $entries, string $objectClass, string $propertyName, ?object $object = null): void
    {
        foreach ($entries as $entry) {
            $key = $entry['key'];
            $contextValue = null;
            
            if (array_key_exists('value', $entry)) {
                // Simple key-value entry
                $contextValue = $entry['value'];
            } elseif (isset($entry['class']) && isset($entry['method'])) {
                // Method-based entry - call the method to get the value
                $contextValue = $this->evaluateContextMethodEntry($entry, $object);
            }
            
            ContextRegistry::set($key, $contextValue);
            
            // Record context set for lineage
            $this->lineageCollector?->recordContextSet(
                $objectClass,
                $propertyName,
                $key,
                $contextValue
            );
        }
    }

    /**
     * Evaluate a method-based context entry.
     * 
     * @param array $entry The context entry with 'class', 'method', and optionally 'args'
     * @param object|null $object The current object being hydrated
     * @return mixed The result of the method call
     */
    private function evaluateContextMethodEntry(array $entry, ?object $object): mixed
    {
        $class = $entry['class'];
        $method = $entry['method'];
        $args = $entry['args'] ?? [];
        
        // Handle ##object reference
        if ($class === '##object') {
            if ($object === null) {
                throw new \RuntimeException('Context entry uses ##object but no object is available');
            }
            $service = $object;
        } else {
            // Resolve the service
            $service = $this->serviceResolver->resolve($class);
        }
        
        // Resolve arguments
        if (!empty($args) && $object !== null) {
            $expressionParser = new ExpressionParser(
                $this->sourceFunctionProviders[$object] ?? new SourceFunctionProvider(
                    fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
                ),
                $this->serviceResolver
            );
            $expressionParser->setCurrentObject($object);
            $propertyLoader = fn(string $propName) => $this->loadProperty($object, $propName);
            $args = $expressionParser->resolveArgs($args, $object, $propertyLoader);
        }
        
        return $service->$method(...$args);
    }

    /**
     * Execute after_instantiating hooks for a newly created object
     *
     * @param object $object
     * @param array $rawData
     */
    private function executeInstantiatingHooks(object $object, array $rawData): void
    {
        $reflectionClass = new ReflectionClass($object);
        $hooks = $this->attributeReader->readPropertyInstantiatingHooks($reflectionClass);
        
        foreach ($hooks as $hook) {
            if ($hook->after_instantiating !== '') {
                $this->executeHook($object, $hook->after_instantiating, $hook->class, $hook->args, $rawData);
            }
        }
    }

    /**
     * Execute hydrating hooks (before/after) for an object
     *
     * @param object $object
     * @param string $when 'before' or 'after'
     * @param array $rawData
     */
    private function executeHydratingHooks(object $object, string $when, array $rawData): void
    {
        $reflectionClass = new ReflectionClass($object);
        $hooks = $this->attributeReader->readPropertyHydratingHooks($reflectionClass);
        
        foreach ($hooks as $hook) {
            if ($when === 'before' && $hook->before_hydrate_object !== '') {
                $this->executeHook($object, $hook->before_hydrate_object, $hook->class, $hook->args, $rawData, false, null, true);
            } elseif ($when === 'after' && $hook->after_hydrate_object !== '') {
                // For after hook, pass both object and rawData as special args
                $this->executeHook($object, $hook->after_hydrate_object, $hook->class, $hook->args, $rawData, true, null, true);
            }
        }
    }

    /**
     * Execute setting hooks for a property
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param string $when 'before' or 'after'
     * @param mixed $propertyValue
     */
    private function executeSettingHooks(object $object, ReflectionProperty $property, string $when, mixed $propertyValue): void
    {
        $hooks = $this->attributeReader->readPropertySettingHooks($property);
        
        foreach ($hooks as $hook) {
            $rawData = [$property->getName() => $propertyValue];
            
            if ($when === 'before' && $hook->before_set_property !== '') {
                $this->executeHook($object, $hook->before_set_property, $hook->class, $hook->args, $rawData, false, $property);
            } elseif ($when === 'after' && $hook->after_set_property !== '') {
                $this->executeHook($object, $hook->after_set_property, $hook->class, $hook->args, $rawData, false, $property);
            }
        }
    }

    /**
     * Execute a hook method
     *
     * @param object $object
     * @param string $method
     * @param string|null $class
     * @param array $args
     * @param array $rawData
     * @param bool $includeObjectInArgs For after_hydrate_object, pass object as first arg
     * @param ReflectionProperty|null $property
     * @param bool $isHydratingHook Whether this is a hydrating hook (receives rawData as arg)
     */
    private function executeHook(
        object $object,
        string $method,
        ?string $class,
        array $args,
        array $rawData,
        bool $includeObjectInArgs = false,
        ?ReflectionProperty $property = null,
        bool $isHydratingHook = false
    ): void {
        // Determine which object/service to call the method on
        $target = $object;
        if ($class !== null) {
            // External service/class
            $target = $this->serviceResolver->resolve($class);
        }
        
        // Resolve hook arguments
        $sourceFunctionProvider = $this->sourceFunctionProviders[$object] ?? new SourceFunctionProvider(
            fn(string $sourceId) => $this->executeDataSourceById($object, $sourceId)
        );
        $expressionParser = new ExpressionParser($sourceFunctionProvider, $this->serviceResolver);
        $expressionParser->setRawData($rawData);
        $expressionParser->setCurrentObject($object);
        
        // Create a property loader callback
        $propertyLoader = function(string $propertyName) use ($object) {
            $this->loadProperty($object, $propertyName);
        };
        
        // Resolve arguments
        $resolvedArgs = [];
        
        // For hydrating hooks, first argument is always rawData
        // For after_hydrate_object, add object as first argument, then rawData
        if ($isHydratingHook) {
            if ($includeObjectInArgs) {
                $resolvedArgs[] = $object;
                $resolvedArgs[] = $rawData;
            } else {
                // For before_hydrate_object, rawData is first argument
                $resolvedArgs[] = $rawData;
            }
        }
        
        foreach ($args as $arg) {
            if ($property !== null && $arg === '#' . $property->getName()) {
                // Special case: reference to the property being set
                $resolvedArgs[] = $property->getValue($object);
            } else {
                // Use standard resolution via expression parser
                $resolved = is_string($arg) ? $this->resolveSingleArg($arg, $object, $propertyLoader, $expressionParser) : $arg;
                $resolvedArgs[] = $resolved;
            }
        }
        
        // Call the hook method
        if (method_exists($target, $method)) {
            // Record hook execution for lineage
            $this->lineageCollector?->recordHookExecution(
                get_class($object),
                $property !== null ? 'property_setting' : ($includeObjectInArgs ? 'hydrating' : 'instantiating'),
                $method,
                $class,
                $resolvedArgs
            );
            
            call_user_func_array([$target, $method], $resolvedArgs);
        }
    }

    /**
     * Resolve a single argument using the expression parser
     *
     * @param string $arg
     * @param object $object
     * @param callable $propertyLoader
     * @param ExpressionParser $expressionParser
     * @return mixed
     */
    private function resolveSingleArg(string $arg, object $object, callable $propertyLoader, ExpressionParser $expressionParser)
    {
        // Handle ##object syntax (return current object)
        if ($arg === '##object') {
            return $object;
        }

        // Handle #property syntax
        if (str_starts_with($arg, '#')) {
            $propertyName = substr($arg, 1);
            $propertyLoader($propertyName);
            
            $reflectionClass = new ReflectionClass($object);
            if ($reflectionClass->hasProperty($propertyName)) {
                $property = $reflectionClass->getProperty($propertyName);
                return $property->getValue($object);
            }
            return null;
        }

        // Handle expr(...) syntax
        if (preg_match('/^expr\((.+)\)$/s', $arg, $matches)) {
            // Re-use the expression parser's existing resolveArgs method
            // which will properly handle the expr() wrapper
            return $expressionParser->resolveArgs([$arg], $object, $propertyLoader)[0];
        }

        // Plain value
        return $arg;
    }


    /**
     * Apply instance-specific mapping to data
     *
     * @param array $data
     * @param Property|null $propertyAttr
     * @return array
     */
    private function applyInstanceMapping(array $data, ?Property $propertyAttr): array
    {
        // If no mapping specified, return data as-is
        if ($propertyAttr === null || $propertyAttr->mapping === null) {
            return $data;
        }
        
        // Transform keys according to mapping
        // mapping format: ['source_key' => 'target_key', ...]
        $mappedData = [];
        foreach ($propertyAttr->mapping as $sourceKey => $targetKey) {
            if (array_key_exists($sourceKey, $data)) {
                $mappedData[$targetKey] = $data[$sourceKey];
            }
        }
        
        return $mappedData;
    }

    /**
     * Validate that CustomHydrator is not combined with conflicting attributes
     *
     * @param ReflectionProperty $property
     * @throws \InvalidArgumentException
     */
    private function validateCustomHydratorExclusivity(ReflectionProperty $property): void
    {
        $conflictingAttributes = [];
        
        if ($this->attributeReader->readProperty($property) !== null) {
            $conflictingAttributes[] = 'Property';
        }
        if ($this->attributeReader->readDataSource($property) !== null) {
            $conflictingAttributes[] = 'DataSource';
        }
        if ($this->attributeReader->readDataSourceRef($property) !== null) {
            $conflictingAttributes[] = 'DataSourceRef';
        }
        if ($this->attributeReader->readSinglePropDataSource($property) !== null) {
            $conflictingAttributes[] = 'SinglePropDataSource';
        }
        
        // Check for MultiPropDataSource on property
        $attrs = $property->getAttributes(MultiPropDataSource::class);
        if (!empty($attrs)) {
            $conflictingAttributes[] = 'MultiPropDataSource';
        }
        
        if (!empty($conflictingAttributes)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'CustomHydrator on property %s::%s cannot be combined with: %s',
                    $property->getDeclaringClass()->getName(),
                    $property->getName(),
                    implode(', ', $conflictingAttributes)
                )
            );
        }
    }

    /**
     * Validate that DataSource attributes are not combined on a property
     * 
     * Rules:
     * - DataSource/SinglePropDataSource + MultiPropDataSource: forbidden  
     * - DataSource/SinglePropDataSource + DataSourceRef: forbidden
     * - MultiPropDataSource + DataSourceRef: forbidden
     * 
     * Note: DataSource is an alias for SinglePropDataSource, so they count as the same attribute.
     *
     * @param ReflectionProperty $property
     * @throws \InvalidArgumentException
     */
    private function validateDataSourceExclusivity(ReflectionProperty $property): void
    {
        $presentAttributes = [];
        
        // DataSource and SinglePropDataSource are aliases, check for either but count as one
        $singlePropAttr = $this->attributeReader->readSinglePropDataSource($property);
        if ($singlePropAttr !== null) {
            // Use the actual class name to be precise in error messages
            $presentAttributes[] = $singlePropAttr instanceof DataSource ? 'DataSource' : 'SinglePropDataSource';
        }
        
        if ($this->attributeReader->readDataSourceRef($property) !== null) {
            $presentAttributes[] = 'DataSourceRef';
        }
        
        // Check for MultiPropDataSource on property
        $attrs = $property->getAttributes(MultiPropDataSource::class);
        if (!empty($attrs)) {
            $presentAttributes[] = 'MultiPropDataSource';
        }
        
        // If more than one DataSource-related attribute, it's an error
        if (count($presentAttributes) > 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Property %s::%s has multiple DataSource attributes which is forbidden: %s. ' .
                    'Only one of DataSource, SinglePropDataSource, MultiPropDataSource, or DataSourceRef is allowed per property.',
                    $property->getDeclaringClass()->getName(),
                    $property->getName(),
                    implode(', ', $presentAttributes)
                )
            );
        }
    }

    /**
     * Load a property using a custom hydrator
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param CustomHydrator $customHydrator
     */
    private function loadPropertyWithCustomHydrator(
        object $object,
        ReflectionProperty $property,
        CustomHydrator $customHydrator
    ): void {
        if (!isset($this->customHydrators[$customHydrator->key])) {
            throw new \RuntimeException(
                sprintf(
                    'Custom hydrator with key "%s" not found. Make sure to register it with DataMapperBuilder->addCustomHydrator()',
                    $customHydrator->key
                )
            );
        }
        
        $callable = $this->customHydrators[$customHydrator->key];
        
        // Get raw data from the current context if available
        // For now, we'll need to get the data from somewhere - this will typically be called
        // during object hydration where raw data is available
        // This is a simplified implementation - in practice, you may need to pass raw data differently
        $rawData = []; // TODO: This needs to be passed from the hydration context
        
        $result = call_user_func($callable, $rawData);
        
        // Validate object class if specified
        if ($customHydrator->objectClass !== null && $result !== null && !($result instanceof $customHydrator->objectClass)) {
            throw new \RuntimeException(
                sprintf(
                    'Custom hydrator "%s" returned object of type %s, but expected %s',
                    $customHydrator->key,
                    get_class($result),
                    $customHydrator->objectClass
                )
            );
        }
        
        // Record custom hydrator for lineage
        $this->lineageCollector?->recordCustomHydrator(
            get_class($object),
            $property->getName(),
            $customHydrator->key,
            $result
        );
        
        // Set the property value
        $this->setPropertyValue($object, $property, $result);
        $this->loadedProperties[$object][$property->getName()] = true;
    }
}
