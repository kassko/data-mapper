<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Validation;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\CustomHydrator;
use Kassko\DataMapper\Metadata\AttributeReader;
use ReflectionClass;

/**
 * Build-time validator for DataMapper metadata attributes.
 * 
 * Validates that data object classes use attributes correctly according to
 * DataMapper rules and constraints.
 */
class MetadataValidator
{
    private AttributeReader $attributeReader;
    private array $errors = [];
    private array $warnings = [];

    public function __construct()
    {
        $this->attributeReader = new AttributeReader();
    }

    /**
     * Validate a single class
     *
     * @param string $className Fully qualified class name
     * @return ValidationResult
     */
    public function validateClass(string $className): ValidationResult
    {
        $this->errors = [];
        $this->warnings = [];

        if (!class_exists($className)) {
            $this->errors[] = "Class {$className} does not exist";
            return new ValidationResult($className, $this->errors, $this->warnings);
        }

        $reflectionClass = new ReflectionClass($className);

        // Validate class-level attributes
        $this->validateClassAttributes($reflectionClass);

        // Validate property-level attributes
        foreach ($reflectionClass->getProperties() as $property) {
            $this->validatePropertyAttributes($reflectionClass, $property);
        }

        return new ValidationResult($className, $this->errors, $this->warnings);
    }

    /**
     * Validate multiple classes
     *
     * @param array<string> $classNames
     * @return array<ValidationResult>
     */
    public function validateClasses(array $classNames): array
    {
        $results = [];
        foreach ($classNames as $className) {
            $results[] = $this->validateClass($className);
        }
        return $results;
    }

    /**
     * Validate class-level attributes
     */
    private function validateClassAttributes(ReflectionClass $class): void
    {
        $className = $class->getName();

        // Validate DataSourcesStore if present
        $store = $this->attributeReader->readDataSourcesStore($class);
        if ($store !== null) {
            foreach ($store->sources as $index => $source) {
                if ($source->id === null) {
                    $this->warnings[] = "{$className}: DataSourcesStore source at index {$index} has no id. It cannot be referenced via DataSourceRef.";
                }

                // Validate that sources in store are properly configured
                if (empty($source->method)) {
                    $this->errors[] = "{$className}: DataSourcesStore source at index {$index} has empty method name.";
                }
            }
        }
    }

    /**
     * Validate property-level attributes
     */
    private function validatePropertyAttributes(ReflectionClass $class, \ReflectionProperty $property): void
    {
        $className = $class->getName();
        $propertyName = $property->getName();
        $context = "{$className}::\${$propertyName}";

        // Check for mutually exclusive attributes
        $this->validateDataSourceExclusivity($context, $property);

        // Validate DataSourceRef if present
        $dataSourceRef = $this->attributeReader->readDataSourceRef($property);
        if ($dataSourceRef !== null) {
            $this->validateDataSourceRef($context, $dataSourceRef, $class);
        }

        // Validate SinglePropDataSource if present
        $singleSource = $this->attributeReader->readSinglePropDataSource($property);
        if ($singleSource !== null) {
            $this->validateSinglePropDataSource($context, $singleSource);
        }

        // Validate MultiPropDataSource if present
        $multiSource = $this->attributeReader->readMultiPropDataSource($property);
        if ($multiSource !== null) {
            $this->validateMultiPropDataSource($context, $multiSource);
        }

        // Validate CustomHydrator if present
        $customHydrator = $this->attributeReader->readCustomHydrator($property);
        if ($customHydrator !== null) {
            $this->validateCustomHydrator($context, $customHydrator, $property);
        }
    }

    /**
     * Validate that only one data source type is used per property
     */
    private function validateDataSourceExclusivity(string $context, \ReflectionProperty $property): void
    {
        $dataSourceTypes = [];

        if ($this->attributeReader->readDataSourceRef($property) !== null) {
            $dataSourceTypes[] = 'DataSourceRef';
        }
        if ($this->attributeReader->readSinglePropDataSource($property) !== null) {
            $dataSourceTypes[] = 'SinglePropDataSource/DataSource';
        }
        if ($this->attributeReader->readMultiPropDataSource($property) !== null) {
            $dataSourceTypes[] = 'MultiPropDataSource';
        }
        if ($this->attributeReader->readCustomHydrator($property) !== null) {
            $dataSourceTypes[] = 'CustomHydrator';
        }

        if (count($dataSourceTypes) > 1) {
            $this->errors[] = "{$context}: Multiple data source types found: " . implode(', ', $dataSourceTypes) . ". Only one is allowed per property.";
        }
    }

    /**
     * Validate DataSourceRef attribute
     */
    private function validateDataSourceRef(string $context, DataSourceRef $ref, ReflectionClass $class): void
    {
        // Validate that referenced sources exist in DataSourcesStore
        if ($ref->id !== null) {
            $this->validateSourceExists($context, $ref->id, $class);
        }

        if ($ref->fallbacks !== null) {
            foreach ($ref->fallbacks as $fallbackId) {
                $this->validateSourceExists($context, $fallbackId, $class);
            }
        }

        if ($ref->providers !== null) {
            foreach ($ref->providers as $providerId) {
                $this->validateSourceExists($context, $providerId, $class);
            }
        }

        // Validate exception class if specified
        if ($ref->exceptionOnNoValidDataSource !== null) {
            if (!class_exists($ref->exceptionOnNoValidDataSource)) {
                $this->errors[] = "{$context}: Exception class '{$ref->exceptionOnNoValidDataSource}' does not exist.";
            } elseif (!is_subclass_of($ref->exceptionOnNoValidDataSource, \Throwable::class)) {
                $this->errors[] = "{$context}: Exception class '{$ref->exceptionOnNoValidDataSource}' must implement Throwable.";
            }
        }
    }

    /**
     * Validate that a source ID exists in DataSourcesStore
     */
    private function validateSourceExists(string $context, string $sourceId, ReflectionClass $class): void
    {
        $store = $this->attributeReader->readDataSourcesStore($class);
        
        if ($store === null) {
            $this->errors[] = "{$context}: References source '{$sourceId}' but no DataSourcesStore is defined on the class.";
            return;
        }

        $found = false;
        foreach ($store->sources as $source) {
            if ($source->id === $sourceId) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->errors[] = "{$context}: References source '{$sourceId}' which does not exist in DataSourcesStore.";
        }
    }

    /**
     * Validate SinglePropDataSource attribute
     */
    private function validateSinglePropDataSource(string $context, SinglePropDataSource|DataSource $source): void
    {
        if (empty($source->method)) {
            $this->errors[] = "{$context}: SinglePropDataSource/DataSource has empty method name.";
        }

        if ($source->class !== null && !class_exists($source->class)) {
            $this->errors[] = "{$context}: Data source class '{$source->class}' does not exist.";
        }
    }

    /**
     * Validate MultiPropDataSource attribute
     */
    private function validateMultiPropDataSource(string $context, MultiPropDataSource $source): void
    {
        if (empty($source->method)) {
            $this->errors[] = "{$context}: MultiPropDataSource has empty method name.";
        }

        if ($source->class !== null && !class_exists($source->class)) {
            $this->errors[] = "{$context}: Data source class '{$source->class}' does not exist.";
        }

        // Validate loading scope
        $validScopes = [
            MultiPropDataSource::SCOPE_ALL,
            MultiPropDataSource::SCOPE_ONLY_KEYS,
            MultiPropDataSource::SCOPE_EXCEPT_KEYS,
            MultiPropDataSource::SCOPE_ONLY_PROPS,
            MultiPropDataSource::SCOPE_EXCEPT_PROPS,
        ];

        if (!in_array($source->loadingScope, $validScopes)) {
            $this->errors[] = "{$context}: Invalid loading scope '{$source->loadingScope}'. Must be one of: " . implode(', ', $validScopes);
        }

        // Validate scope configuration
        if ($source->loadingScope === MultiPropDataSource::SCOPE_ONLY_KEYS && empty($source->loadingScopeKeys)) {
            $this->warnings[] = "{$context}: SCOPE_ONLY_KEYS is set but loadingScopeKeys is empty. No properties will be loaded.";
        }

        if ($source->loadingScope === MultiPropDataSource::SCOPE_ONLY_PROPS && empty($source->loadingScopeProps)) {
            $this->warnings[] = "{$context}: SCOPE_ONLY_PROPS is set but loadingScopeProps is empty. No properties will be loaded.";
        }
    }

    /**
     * Validate CustomHydrator attribute
     */
    private function validateCustomHydrator(string $context, CustomHydrator $hydrator, \ReflectionProperty $property): void
    {
        // Validate that other data source attributes are not present
        if ($this->attributeReader->readDataSourceRef($property) !== null ||
            $this->attributeReader->readSinglePropDataSource($property) !== null ||
            $this->attributeReader->readMultiPropDataSource($property) !== null) {
            $this->errors[] = "{$context}: CustomHydrator cannot be used with other data source attributes.";
        }
    }
}
