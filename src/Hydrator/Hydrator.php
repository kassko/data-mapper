<?php

namespace Kassko\DataMapper\Hydrator;

use Exception;
use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration\ObjectKey;
use Kassko\DataMapper\Configuration\RuntimeConfiguration;
use Kassko\DataMapper\Exception\NotFoundMemberException;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Expression\ExpressionLanguage;
use Kassko\DataMapper\Hydrator\Exception\NotResolvableValueException;
use Kassko\DataMapper\Hydrator\ExpressionLanguageEvaluator;
use Kassko\DataMapper\Hydrator\MemberAccessStrategy;
use Kassko\DataMapper\ObjectManager;
use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;
use \DateTimeInterface;

/**
* An object hydrator.
*
* @author kko
*/
class Hydrator extends AbstractHydrator
{
    const SUFFIXE_EXTRACTION_RELATION = '_';//<= A revoir !

    /**
     * @var bool
     */
    protected $isPropertyAccessStrategyOn = true;

    /**
     * @var \Kassko\DataMapper\Hydrator\MemberAccessStrategy\MemberAccessStrategyInterface
     */
    protected $memberAccessStrategy;

    /**
     * @var \Kassko\DataMapper\Hydrator\MemberAccessStrategy\MemberAccessStrategyInterface
     */
    protected $propertyAccessStrategy;

    /**
     * Track properties already hydrated. Only properties hydrated by data sources.
     * @var bool[]
     */
    private $dataSourceLoadingDone;

    /**
     * Track properties already hydrated. Only properties hydrated by providers.
     * @var bool[]
     */
    private $providerLoadingDone;

    /**
     * Retrieve an object instance from it's class name.
     * @var ClassResolverInterface
     */
    private $classResolver;

    /**
     * Retrieve an object instance from it's class name.
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * Evaluate expression language.
     * @var ExpressionLanguageEvaluator
     */
    private $expressionLanguageEvaluator;

    /**
     * Contains all the expression context variables.
     * @var ExpressionContext
     */
    private $expressionContext;

    /**
     * Contains the parent of the object currently hydrated.
     * @var object
     */
    private $parentOfObjectCurrentlyHydrated;

    /**
     * All the variables used in a object mapping configuration.
     * @var array
     */
    private $currentConfigVariables = [];

    /**
     * The context of the current hydration.
     * @var HydratorContext
     */
    private $currentHydrationContext;

    /**
     * @var MethodInvoker
     */
    private $methodInvoker;

    /**
    * Constructor
    *
    * @param ObjectManager $objectManager The ObjectManager to use
    * @param bool $isPropertyAccessStrategyOn If set to false, hydrator will always use entity's public API
    */
    public function __construct(ObjectManager $objectManager, $isPropertyAccessStrategyOn)
    {
        parent::__construct($objectManager);

        $this->isPropertyAccessStrategyOn = $isPropertyAccessStrategyOn;
        $this->expressionLanguageEvaluator = $this->objectManager->getExpressionLanguageEvaluator();
        $this->expressionContext = $this->objectManager->getExpressionContext();
        $this->methodInvoker = $this->objectManager->getMethodInvoker();
    }

    /**
     * Sets a class resolver.
     *
     * @param ClassResolverInterface $classResolver A class resolver 
     *
     * @return self
     */
    public function setClassResolver(ClassResolverInterface $classResolver)
    {
        $this->classResolver = $classResolver;

        return $this;
    }

    /**
     * Unsets the class resolver.
     *
     * @return self
     */
    public function unsetClassResolver()
    {
        $this->classResolver = null;

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function extract($object, ObjectKey $objectKey = null)
    {
        $this->prepare($object, $objectKey);
        return $this->doExtract($object);
    }

    /**
    * {@inheritdoc}
    */
    public function hydrate(array $data, $object, ObjectKey $objectKey = null)
    {
        $this->prepare($object, $objectKey);
        return $this->doHydrate($data, $object);
    }

    /**
    * {@inheritdoc}
    */
    public function getRelationFieldNameExtraction($relationFieldName)
    {
        return $relationFieldName.self::SUFFIXE_EXTRACTION_RELATION;
    }

    /**
    * Extract data from an object.
    *
    * @param object $object
    * @throws RuntimeException
    * @return array
    */
    protected function doExtract($object)
    {
        $originalFieldNames = $this->metadata->getOriginalFieldNames();

        /*
        $filter = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;
        */

        $data = [];
        if (! $this->metadata->hasCustomHydrator()) {

            $this->executeMethods($object, $this->metadata->getPreExtractListeners());

            foreach ($originalFieldNames as $originalFieldName) {
                $mappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);

                if ($this->metadata->isNotManaged($mappedFieldName)) {
                    continue;
                }

                if ($this->metadata->isValueObject($mappedFieldName)) {
                    continue;
                }

                /*if ($filter && !$filter->filter($mappedFieldName)) {
                    continue;
                }*/

                try {
                    $value = $this->memberAccessStrategy->getValue($object, $mappedFieldName);             
                } catch (NotFoundMemberException $e) {
                    continue;
                }

                if (null === $value) {
                    $data[$originalFieldName] = null;
                    continue;
                }

                if ($fieldClass = $this->metadata->getClassOfMappedField($mappedFieldName)) {
                    $fieldHydrator = $this->objectManager->createHydratorFor($fieldClass);
                    $fieldValue = $value;

                    /*if (! is_array($fieldValue)) {
                        throw new ObjectMappingException(
                            sprintf(
                                'Cannot hydrate field "%s" of class "%s" from raw data.'
                                . ' Raw data should be an array but got "%s".', 
                                $mappedFieldName,
                                $fieldClass,
                                is_object($fieldValue) ? get_class($fieldValue) : gettype($fieldValue)
                                )
                        );
                    }   */
                    
                    reset($fieldValue);
                    if (0 !== count($fieldValue) && ! is_numeric(key($fieldValue))) {
                        $fieldRawData = $fieldHydrator->extract($fieldValue);
                        $data[$originalFieldName] = $fieldRawData;                              
                    } else {

                        $fieldRawData = [];
                        foreach ($fieldValue as $itemFieldValue) {              
                            $fieldRawData[] = $fieldHydrator->extract($itemFieldValue);                
                        }
                        $data[$originalFieldName] = $fieldRawData;
                    }   
                } else {
                    $value = $this->extractValue($mappedFieldName, $value, $object, $data);
                    $data[$originalFieldName] = $value;    
                }
            }
        } else {
            list($customHydratorClass, , $customExtractMethod) = $this->metadata->getCustomHydratorInfo();            
            $this->executeMethod($object, new ClassMetadata\Model\Method($customHydratorClass, $customExtractMethod));
        }

        //Value objects extraction.
        foreach ($this->metadata->getFieldsWithValueObjects() as $mappedFieldName) {

            if ($this->metadata->isNotManaged($mappedFieldName)) {
                continue;
            }

            $info = $this->metadata->getValueObjectInfo($mappedFieldName);
            list($voClassName, $voResource, $voResourceType) = $info;

            $valueObjectHydrator = $this->objectManager->createHydratorFor($voClassName);
            $objectKey = $this->pushRuntimeConfiguration($mappedFieldName, $object, $voClassName, $voResourceType, $voResource);

            $valueObject = $this->memberAccessStrategy->getValue($object, $mappedFieldName);
            $valueObjectData = $valueObjectHydrator->extract($valueObject, $objectKey);
            $this->popRuntimeConfiguration();

            $data = array_merge($data, $valueObjectData);
        }

        $this->executeMethods($object, $this->metadata->getPostExtractListeners());

        return $data;
    }

    /**
    * Hydrate an object from data.
    *
    * @param array $data
    * @param object $object
    * @return object
    */
    protected function doHydrate(array $data, $object)
    {
        $previousHydrationContext = $this->currentHydrationContext;
        $this->currentHydrationContext = new CurrentHydrationContext($data, $object);

        foreach ($this->metadata->getMappedFieldNames() as $mappedFieldName) {
            $defaultValue = $this->metadata->getFieldDefaultValue($mappedFieldName);

            if (null !== $defaultValue) {
                $this->resolveValue($defaultValue, $object);
                $this->memberAccessStrategy->setValue($defaultValue, $object, $mappedFieldName);   
            }
        }

        if (! $this->metadata->hasCustomHydrator()) {

            $this->executeMethods($object, $this->metadata->getPreHydrateListeners());

            foreach ($data as $originalFieldName => $value) {
                $mappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);
                if (null === $mappedFieldName) {

                    //It's possible that a raw field name has no corresponding field name
                    //because a raw field can be a value object part.
                    continue;
                }

                if (! $this->metadata->hasSource($mappedFieldName)) {
                    //Classical hydration.
                    $this->walkHydration($mappedFieldName, $object, $value, $data);
                }
            }
        } else {
            list($customHydratorClass, $customHydrateMethod) = $this->metadata->getCustomHydratorInfo();
            $this->executeMethod($object, new ClassMetadata\Model\Method($customHydratorClass, $customHydrateMethod));
        }

        //DataSources hydration.
        foreach ($this->metadata->getFieldsWithDataSources() as $mappedFieldName) {

            if ($this->metadata->hasDataSource($mappedFieldName)) {//<= Is this test usefull ?
                $this->walkHydrationByDataSource($mappedFieldName, $object, false);
            }
        }

        //Providers hydration.
        foreach ($this->metadata->getFieldsWithProviders() as $mappedFieldName) {

            if ($this->metadata->hasProvider($mappedFieldName)) {//<= Is this test usefull ?
                $this->walkHydrationByProvider($mappedFieldName, $object, false);
            }
        }

        //Value objects hydration.
        foreach ($this->metadata->getFieldsWithValueObjects() as $mappedFieldName) {
            $this->walkValueObjectHydration($mappedFieldName, $object, $data);
        }

        $this->executeMethods($object, $this->metadata->getPostHydrateListeners());

        $this->currentHydrationContext = $previousHydrationContext;

        return $object;
    }

    public function load($object)
    {
        $this->hydrate([], $object);
    }

    public function loadProperty($object, $mappedFieldName)
    {
        $this->prepare($object);
        
        if ($this->metadata->hasDataSource($mappedFieldName)) {
            $this->walkHydrationByDataSource($mappedFieldName, $object, true);
        } elseif ($this->metadata->hasProvider($mappedFieldName)) {
            $this->walkHydrationByProvider($mappedFieldName, $object, true);
        } else {
            throw ObjectMappingException::notFoundAssociation($mappedFieldName, $this->metadata->getName());
        }

        $this->objectManager->markPropertyLoaded($object, $mappedFieldName);
    }

    public function hydrateProperty($object, $mappedFieldName, $data, $defaultValueToSet = null)
    {
        $this->prepare($object);

        $originalFieldName = $this->metadata->getOriginalFieldName($mappedFieldName);

        if (! isset($data[$originalFieldName])) {
            $value = $defaultValueToSet;
        } else {
            $value = $data[$originalFieldName];
        }

        $this->walkHydration($mappedFieldName, $object, $value, $data);
    }

    public function extractProperty($object, $mappedFieldName, $data = null, $bypassLoading = false)
    {
        $this->prepare($object);

        $memberAccessStrategy = $this->guessMemberAccessStrategyFromLoadingStrategy($bypassLoading);
        $value = $memberAccessStrategy->getValue($object, $mappedFieldName);

        return $this->extractValue($mappedFieldName, $value, $object, $data);
    }

    public function findFromSource(ClassMetadata\Model\Source $sourceMetadata)
    {
        if (null === $sourceMetadata->getFallbackSourceId()) {
            return $this->objectManager->findFromSource($sourceMetadata);
        }

        if (ClassMetadata\Model\Source::ON_FAIL_CHECK_RETURN_VALUE === $sourceMetadata->getOnFail()) {
            
            $data = $this->objectManager->findFromSource($sourceMetadata);
            if ($sourceMetadata->areDataInvalid($data)) {
                $sourceMetadata = $this->metadata->findSourceById($sourceMetadata->getFallbackSourceId());
                return $this->findFromSource($sourceMetadata);
            }

            return $data;
        } 

        //Else ClassMetadata\Model\Source::ON_FAIL_CHECK_EXCEPTION === $sourceMetadata->getOnFail().
        try {
            $data = $this->objectManager->findFromSource($sourceMetadata);
        } catch (Exception $e) {
            $exceptionClass = $sourceMetadata->getExceptionClass();
            if (! $e instanceof $exceptionClass) {
                throw $e;
            }
            $sourceMetadata = $this->metadata->findSourceById($sourceMetadata->getFallbackSourceId());
            return $this->findFromSource($sourceMetadata);
        }

        return $data;  
    }

    protected function walkHydration($mappedFieldName, $object, $value, $data)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        if (! $this->hasStrategy($mappedFieldName)) {
            $value = $this->handleTypeConversions($value, $this->metadata->getTypeOfMappedField($mappedFieldName));
        }
        
        if (null === $value) {
            $this->memberAccessStrategy->setValue(null, $object, $mappedFieldName);
            return true;
        }

        if ($fieldClass = $this->metadata->getClassOfMappedField($mappedFieldName)) {

            if (is_object($value)) {
                if (! $value instanceof $fieldClass) {
                    throw new ObjectMappingException(
                        sprintf(
                            'The class of field "%s::%s" is "%s".'
                            . ' Cannot cast "%s" to "%s".', 
                            get_class($object),
                            $mappedFieldName,
                            get_class($value),
                            get_class($value),
                            $fieldClass
                        )
                    );
                }
                 
                $this->memberAccessStrategy->setValue($value, $object, $mappedFieldName);
                return true;
            }

            if (! is_array($value)) {
                throw new ObjectMappingException(
                    sprintf(
                        'Cannot hydrate field "%s::%s" from raw data.'
                        . ' Raw data should be an array but got "%s".', 
                        $fieldClass,
                        $mappedFieldName,
                        is_object($value) ? get_class($value) : gettype($value)
                    )
                );
            }

            $hasConfig = $this->metadata->isValueObject($mappedFieldName);
            $fieldHydrator = $this->createFieldHydrator($fieldClass, $object, $mappedFieldName, $hasConfig);

            $this->parentOfObjectCurrentlyHydrated = $object;

            reset($value);
            if (0 !== count($value) && ! is_numeric(key($value))) {
                $field = new $fieldClass;
                $fieldHydrator->hydrate($value, $field);
                $this->memberAccessStrategy->setValue($field, $object, $mappedFieldName);                               
            } else {
                $fieldResult = [];
                foreach ($value as $record) {              
                    $field = new $fieldClass;
                    $fieldResult[] = $fieldHydrator->hydrate($record, $field);                   
                }
                $this->memberAccessStrategy->setValue($fieldResult, $object, $mappedFieldName);
            }

            $this->parentOfObjectCurrentlyHydrated = null;    

            if ($hasConfig) {
                $this->popRuntimeConfiguration(); 
            }

            return true;       
        } 

        $value = $this->hydrateValue($mappedFieldName, $value, $data, $object);        

        if (! $this->metadata->isMappedDateField($mappedFieldName)) {
            $type = $this->metadata->getTypeOfMappedField($mappedFieldName);
            
            if (null !== $type) {
                if (! is_array($value)) {
                    $this->setType($value, $type, $this->metadata->getName(), $mappedFieldName);    
                } /*else {
                    foreach ($value as &$itemValue) {
                         $this->setType($itemValue, $type);    
                    }
                }*/
            }
            
        } else {
            //We do not call the setter for a DateTime when the value to defined is null or empty.
            //We do so instead of initialize this DateTime to the current date.
            if (empty($value)) {
                return true;
            }
        }

        $this->memberAccessStrategy->setValue($value, $object, $mappedFieldName);    

        return true;
    }

    private function setType(&$value, $type, $objectClass, $mappedFieldName)
    {
        static $allowedTypes = ['boolean', 'bool', 'int', 'integer', 'float', 'string'];
        if (! in_array($type, $allowedTypes)) {
            throw ObjectMappingException::badConversionType($type, $allowedTypes, $objectClass, $mappedFieldName);
        }

        \settype($value, $type);
    }

    protected function createFieldHydrator($fieldClass, $object, $mappedFieldName, $hasConfig)
    {
        if (! $hasConfig) {
            $fieldHydrator = $this->objectManager->createHydratorFor($fieldClass);
        } else {
            list($voClassName, $voResourceName, $voResourceType) = $this->metadata->getValueObjectInfo($mappedFieldName);
            $objectKey = $this->pushRuntimeConfiguration($mappedFieldName, $object, $voClassName, $voResourceName, $voResourceType);
            $fieldHydrator = $this->objectManager->createHydratorFor($objectKey);
        }

        if ($this->metadata->fieldHasVariables($mappedFieldName)) {
            $fieldHydratorConfigVariables = $this->metadata->getVariablesByField($mappedFieldName);
            $this->resolveValues($fieldHydratorConfigVariables, $object);
            $fieldHydrator->setCurrentConfigVariables(array_merge($this->currentConfigVariables, $fieldHydratorConfigVariables));    
        }

        return $fieldHydrator;
    }

    protected function walkHydrationByDataSource($mappedFieldName, $object, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        if ($this->objectManager->isPropertyLoaded($object, $mappedFieldName)) {
            return;
        }

        $sourceMetadata = $this->metadata->getDataSourceInfo($mappedFieldName);

        if (! $enforceLoading && $sourceMetadata->getLazyLoading()) {
            return;
        }

        $this->walkHydrationByDataSourceMetadata($sourceMetadata, $mappedFieldName, $object, $enforceLoading);            
    }

    protected function walkHydrationByDataSourceMetadata($sourceMetadata, $mappedFieldName, $object, $enforceLoading)
    {
        $args = $sourceMetadata->getMethod()->getArgs();
        $this->resolveValues($args, $object);
        $sourceMetadata->getMethod()->setArgs($args);
        $data = $this->findFromSource($sourceMetadata);
        
        $this->executeMethods($object, $sourceMetadata->getPreprocessors());

        if (! $sourceMetadata->getSupplySeveralFields()) {
            $this->walkHydration($mappedFieldName, $object, $data, $data);
        } else {
            $mappedFieldsToHydrate = array_merge([$mappedFieldName], $this->metadata->getFieldsWithSameDataSource($mappedFieldName));
            foreach ($data as $originalFieldName => $value) {
        
                $otherMappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);
                if (! in_array($otherMappedFieldName, $mappedFieldsToHydrate)) {
                    continue;
                }
                
                $this->walkHydration($otherMappedFieldName, $object, $value, $data);                    
            }
        }

        if ($sourceMetadata->hasDepends()) {
            foreach ($sourceMetadata->getDepends() as $dependFieldName) {
                $sourceMetadata = $this->metadata->getSourceInfo($dependFieldName);
                if ($sourceMetadata instanceof \Kassko\DataMapper\ClassMetadata\Model\DataSource) {
                    $this->walkHydrationByDataSourceMetadata($sourceMetadata, $dependFieldName, $object, $enforceLoading);
                } else {
                    $this->walkHydrationByProviderMetadata($sourceMetadata, $dependFieldName, $object, $enforceLoading);
                }
            }
        }

        $this->executeMethods($object, $sourceMetadata->getProcessors());
    }

    protected function walkHydrationByProvider($mappedFieldName, $object, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        if ($this->objectManager->isPropertyLoaded($object, $mappedFieldName)) {
            return;
        }

        $sourceMetadata = $this->metadata->getProviderInfo($mappedFieldName);
        
        if (! $enforceLoading && $sourceMetadata->getLazyLoading()) {
            return;
        }

        $this->walkHydrationByProviderMetadata($sourceMetadata, $mappedFieldName, $object, $enforceLoading);
    }

    protected function walkHydrationByProviderMetadata($sourceMetadata, $mappedFieldName, $object, $enforceLoading)
    {
        $args = $sourceMetadata->getMethod()->getArgs();
        $this->resolveValues($args, $object);
        $sourceMetadata->getMethod()->setArgs($args);
        $data = $this->findFromSource($sourceMetadata);

        $this->executeMethods($object, $sourceMetadata->getPreprocessors());
        
        if (! $sourceMetadata->getSupplySeveralFields()) {
            $this->memberAccessStrategy->setValue($data, $object, $mappedFieldName);
        } else {
            $mappedFieldsToHydrate = array_merge([$mappedFieldName], $this->metadata->getFieldsWithSameProvider($mappedFieldName));

            foreach ($data as $originalFieldName => $value) {
            
                $otherMappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);

                if (! in_array($otherMappedFieldName, $mappedFieldsToHydrate)) {
                    continue;
                }

                $this->memberAccessStrategy->setValue($value, $object, $mappedFieldName);                 
            }
        }

        if ($sourceMetadata->hasDepends()) {
            foreach ($sourceMetadata->getDepends() as $dependFieldName) {
                $sourceMetadata = $this->metadata->getSourceInfo($dependFieldName);
                if ($sourceMetadata instanceof \Kassko\DataMapper\ClassMetadata\Model\DataSource) {
                    $this->walkHydrationByDataSourceMetadata($sourceMetadata, $dependFieldName, $object, $enforceLoading);
                } else {
                    $this->walkHydrationByProviderMetadata($sourceMetadata, $dependFieldName, $object, $enforceLoading);
                }
            }
        }

        $this->executeMethods($object, $sourceMetadata->getProcessors());
    }

    protected function walkValueObjectHydration($mappedFieldName, $object, $data)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        list($voClassName, $voResourceName, $voResourceType) = $this->metadata->getValueObjectInfo($mappedFieldName);

        $objectKey = $this->pushRuntimeConfiguration($mappedFieldName, $object, $voClassName, $voResourceName, $voResourceType);
        $valueObjectHydrator = $this->objectManager->createHydratorFor($objectKey);

        $valueObject = new $voClassName;
        $this->memberAccessStrategy->setValue($valueObject, $object, $mappedFieldName);
        $result = $valueObjectHydrator->hydrate($data, $valueObject, $objectKey);
        $this->popRuntimeConfiguration();

        return $result;
    }

    protected function resolveValues(array &$args, $object)
    {
        if (0 === count($args)) {
            return;
        }

        if (null !== $this->expressionContext) {
            $this->expressionContext['value_resolver'] = $this->valueResolver;
            $this->expressionContext['this'] = $object;
        }

        foreach ($args as &$arg) {
            try {
                $arg = $this->valueResolver->handle($arg, $object);
                continue;//$arg is resolved because no exception is thrown. So next loop.
            } catch (NotResolvableValueException $e) {              
            }
            
            //$arg is not resolved, we try to resolve it with another resolver.
            if (null !== $this->expressionLanguageEvaluator) {
                try {
                    $arg = $this->expressionLanguageEvaluator->handle($arg);
                    //$arg is resolved with the second resolver.
                } catch (NotResolvableValueException $e) {
                    //$arg is not resolved. We assumes it doesn't need to be resolved. Next loop.
                }
            }
        }
    }

    protected function computeSourceKey($objectClass, ClassMetadata\Model\Source $sourceMetadata)
    {
        if (null === $sourceMetadata->getId()) {
            return $objectClass . $sourceMetadata->getMethod()->getClass() . $sourceMetadata->getMethod()->getFunction();
        } 

        return $objectClass . $sourceMetadata->getId() . $sourceMetadata->getMethod()->getClass() . $sourceMetadata->getMethod()->getFunction();
    }

    /**
     * @param object $object
     * @param ClassMetadata\Model\Method[] $method
     */
    private function executeMethods($object, array $methods)
    {
        foreach ($methods as $method) {
            $this->executeMethod($object, $method);
        }
    }

    /**
     * @param object $object
     * @param ClassMetadata\Model\Method $method
     */
    private function executeMethod($object, ClassMetadata\Model\Method $method)
    {
        $class = $method->getClass();

        $classInArgs = [$class];
        $this->resolveValues($classInArgs, $object);

        if (is_object($classInArgs[0])) {//If expression present in $class is resolved to an object.
            $instance = $classInArgs[0];
        } else {
            $instance = $this->classResolver ? $this->classResolver->resolve($class) : new $class;
        }

        $args = $method->getArgs();
        $this->resolveValues($args, $object);
        $this->methodInvoker->invoke($instance, $method->getFunction(), $args);
    }

    protected function pushRuntimeConfiguration($mappedFieldName, $object, $voClassName, $voResource, $voResourceType)
    {
        if (null === $voResourceType) {
            return;
        }

        $objectKey = new ObjectKey($voClassName, get_class($object), $mappedFieldName);
        $runtimeConfiguration = (new RuntimeConfiguration)->addMappingResourceInfo($objectKey, $voResource, $voResourceType);
        $this->objectManager->getConfiguration()->pushRuntimeConfiguration($runtimeConfiguration);

        return $objectKey;
    }

    protected function popRuntimeConfiguration()
    {
        $this->objectManager->getConfiguration()->popRuntimeConfiguration();
    }

    protected function doPrepare($object, ObjectKey $objectKey = null)
    {
        if (isset($object)) {
            $this->propertyAccessStrategy = $this->createPropertyAccessStrategy($object);
            $this->memberAccessStrategy = $this->createMemberAccessStrategy($object, $this->propertyAccessStrategy); 
            $this->valueResolver = new ValueResolver($this, $this->metadata, $this->classResolver);
        }
    }

    private function createMemberAccessStrategy($object, MemberAccessStrategy\PropertyAccessStrategy $propertyAccessStrategy)
    {
        $memberAccessStrategy = 
        $this->isPropertyAccessStrategyOn ? 
        $propertyAccessStrategy : 
        new MemberAccessStrategy\GetterSetterAccessStrategy($propertyAccessStrategy)
        ;
        
        $memberAccessStrategy->prepare($object, $this->metadata);

        return $memberAccessStrategy;
    }

    private function createPropertyAccessStrategy($object)
    {
        $propertyAccessStrategy = new MemberAccessStrategy\PropertyAccessStrategy;
        $propertyAccessStrategy->prepare($object, $this->metadata);

        return $propertyAccessStrategy;
    }

    private function guessMemberAccessStrategyFromLoadingStrategy($byPassLoading)
    {
        if ($byPassLoading) {
            return $this->propertyAccessStrategy;
        }

        return $this->memberAccessStrategy;
    }

    /**
     * Gets all the variables used in a object mapping configuration.
     *
     * @return array
     */
    public function getCurrentConfigVariableByName($variableKey)
    {
        if (! isset($this->currentConfigVariables[$variableKey])) {
            throw new ObjectMappingException(
                sprintf(
                    'The current config variables do not contains key "%s". Availables keys are "[%s]".',
                    $variableKey,
                    implode(',', array_keys($this->currentConfigVariables))
                )
            );
        }
        
        return $this->currentConfigVariables[$variableKey];
    }

    /**
     * Sets all the variables used in a object mapping configuration.
     *
     * @param array $currentConfigVariables the current config variables
     *
     * @return self
     */
    public function setCurrentConfigVariables(array $currentConfigVariables)
    {
        $this->currentConfigVariables = $currentConfigVariables;

        return $this;
    }

    /**
     * Gets the raw data currently hydrated.
     *
     * @return array
     */
    public function getCurrentRawData()
    {
        return $this->currentHydrationContext->getData();
    }

    /**
     * Gets the raw data currently hydrated.
     *
     * @return array
     */
    public function getCurrentObject()
    {
        return $this->currentHydrationContext->getObject();
    }

    /**
     * Gets the raw data currently hydrated.
     *
     * @return object
     */
    public function getParentOfObjectCurrentlyHydrated()
    {
        return $this->parentOfObjectCurrentlyHydrated;
    }
}
