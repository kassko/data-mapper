<?php

namespace Kassko\DataMapper\Hydrator;

use DateTime;
use Exception;
use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\ClassMetadata\SourcePropertyMetadata;
use Kassko\DataMapper\Configuration\ObjectKey;
use Kassko\DataMapper\Configuration\RuntimeConfiguration;
use Kassko\DataMapper\Exception\NotFoundMemberException;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Hydrator\MemberAccessStrategy;
use Kassko\DataMapper\ObjectManager;
use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;

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
     * Track properties already hydrated. Only properties hydrated by data sources.
     */
    private $dataSourceLoadingDone;

    /**
     * Track properties already hydrated. Only properties hydrated by providers.
     */
    private $providerLoadingDone;

    /**
     * Retrieve an object instance from it's class name.
     */
    private $classResolver;

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
    }

    public function setClassResolver(ClassResolverInterface $classResolver)
    {
        $this->classResolver = $classResolver;

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
            $customHydrator = $this->classResolver ? $this->classResolver->resolve($customHydratorClass) : new $customHydratorClass;
            $data = $customHydrator->$customExtractMethod($object);
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

        return $data;
    }

    /**
    * Hydrate an object from data.
    *
    * @param array $data
    * @param object $object
    * @throws RuntimeException
    * @return object
    */
    protected function doHydrate(array $data, $object)
    {
        if (! $this->metadata->hasCustomHydrator()) {

            foreach ($data as $originalFieldName => $value) {
                $mappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);
                if (null === $mappedFieldName) {

                    //It's possible that a raw field name has no corresponding field name
                    //because a raw field can be a value object part.
                    continue;
                }

                if (! $this->metadata->hasDataSource($mappedFieldName)) {
                    //Classical hydration.
                    $this->walkHydration($mappedFieldName, $object, $value, $data);
                }
            }
        } else {

            list($customHydratorClass, $customHydrateMethod) = $this->metadata->getCustomHydratorInfo();
            $customHydrator = $this->classResolver ? $this->classResolver->resolve($customHydratorClass) : new $customHydratorClass;
            $customHydrator->$customHydrateMethod($data, $object);
        }

        //DataSources hydration.
        $this->dataSourceLoadingDone = [];
        foreach ($this->metadata->getFieldsWithDataSources() as $mappedFieldName) {

            if ($this->metadata->hasDataSource($mappedFieldName)) {//<= Is this test usefull ?
                $this->walkHydrationByDataSource($mappedFieldName, $object, false);
            }
        }

        //Providers hydration.
        $this->providerLoadingDone = [];
        foreach ($this->metadata->getFieldsWithProviders() as $mappedFieldName) {

            if ($this->metadata->hasProvider($mappedFieldName)) {//<= Is this test usefull ?
                $this->walkHydrationByProvider($mappedFieldName, $object, false);
            }
        }

        //Value objects hydration.
        foreach ($this->metadata->getFieldsWithValueObjects() as $mappedFieldName) {
            $this->walkValueObjectHydration($mappedFieldName, $object, $data);
        }

        return $object;
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

    public function extractProperty($object, $mappedFieldName, $data = null)
    {
        $this->prepare($object);
        $value = $this->memberAccessStrategy->getValue($object, $mappedFieldName);

        return $this->extractValue($mappedFieldName, $value, $object, $data);
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

      	    if (! is_array($value)) {
            	throw new ObjectMappingException(
        		    sprintf(
        		        'Cannot hydrate field "%s" of class "%s" from raw data.'
        		        . ' Raw data should be an array but got "%s".', 
        		        $mappedFieldName,
        		        $fieldClass,
        		        is_object($value) ? get_class($value) : gettype($value)
            		    )
        		);
            }   

            
            $fieldHydrator = $this->objectManager->createHydratorFor($fieldClass);
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

            return true;       
        } 

        $value = $this->hydrateValue($mappedFieldName, $value, $data, $object);        

        if (! $this->metadata->isMappedDateField($mappedFieldName)) {
            settype($value, $this->metadata->gettypeOfMappedField($mappedFieldName));
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

    protected function findFromSource(SourcePropertyMetadata $sourceMetadata)
    {
        if (! isset($sourceMetadata->fallbackSourceId)) {
            return $this->objectManager->findFromSource($sourceMetadata->class, $sourceMetadata->method, $sourceMetadata->args);
        }

        if (SourcePropertyMetadata::ON_FAIL_CHECK_RETURN_VALUE === $sourceMetadata->onFail) {
            
            $data = $this->objectManager->findFromSource($sourceMetadata->class, $sourceMetadata->method, $sourceMetadata->args);
            if ($sourceMetadata->areDataInvalid($data)) {
                $sourceMetadata = $this->metadata->findSourceById($sourceMetadata->fallbackSourceId);
                return $this->findFromSource($sourceMetadata);
            }

            return $data;
        } 

        //Else SourcePropertyMetadata::ON_FAIL_CHECK_EXCEPTION === $sourceMetadata->onFail.
        try {
            $data = $this->objectManager->findFromSource($sourceMetadata->class, $sourceMetadata->method, $sourceMetadata->args);
        } catch (Exception $e) {
            if (! $e instanceof $sourceMetadata->exceptionClass) {
                throw $e;
            }
            $sourceMetadata = $this->metadata->findSourceById($sourceMetadata->fallbackSourceId);
            return $this->findFromSource($sourceMetadata);
        }

        return $data;  
    }

    protected function walkHydrationByDataSource($mappedFieldName, $object, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        $sourceMetadata = $this->metadata->getDataSourceInfo($mappedFieldName);
        
        $key = $this->computeSourceKey(get_class($object), $sourceMetadata->id, $sourceMetadata->class, $sourceMetadata->method) . spl_object_hash($object);

        //Checks if hash is orphan.
        //This is possible because when a object dead, it's hash is reused on another object. 
        if (false === $object->__isRegistered) {    
            unset($this->dataSourceLoadingDone[$key]); 
        }

        if (! isset($this->dataSourceLoadingDone[$key]) && ($enforceLoading || ! $sourceMetadata->lazyLoading)) {

            $this->resolveMethodArgs($sourceMetadata->args, $object, $mappedFieldName);
            $data = $this->findFromSource($sourceMetadata);
            
            if (! $sourceMetadata->supplySeveralFields) {
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

            $this->dataSourceLoadingDone[$key] = true;
        }            
    }

    protected function walkHydrationByProvider($mappedFieldName, $object, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        $sourceMetadata = $this->metadata->getProviderInfo($mappedFieldName);
        
        $key = $this->computeSourceKey(get_class($object), $sourceMetadata->id, $sourceMetadata->class, $sourceMetadata->method) . spl_object_hash($object);

        //Checks if hash is orphan.
        //This is possible because when a object dead, it's hash is reused on another object. 
        if (false === $object->__isRegistered) {
            unset($this->providerLoadingDone[$key]); 
        }

        if (! isset($this->providerLoadingDone[$key]) && ($enforceLoading || ! $sourceMetadata->lazyLoading)) {

            $this->resolveMethodArgs($sourceMetadata->args, $object, $mappedFieldName);
            $data = $this->findFromSource($sourceMetadata);
            
            if (! $sourceMetadata->supplySeveralFields) {
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

            $this->providerLoadingDone[$key] = true;
        }            
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

    protected function resolveMethodArgs(array &$args, $object, $mappedFieldName)
    {
        if (0 === count($args)) {
            return;
        }

        foreach ($args as &$arg) {

            if ('##this' === $arg) {
                $arg = $object; 
            } /*elseif ('##value' === $arg) {
                $arg = $object; 
            }*/ elseif ('#' === $arg[0]) {
                $argsMappedFieldName = $this->metadata->getMappedFieldName(substr($arg, 1));
                $arg = $this->extractProperty($object, $argsMappedFieldName);  
            } elseif ('@' === $arg[0]) {
                if ($this->classResolver) {
                    $arg = $this->classResolver->resolve($arg);
                } else {
                    throw new ObjectMappingException(sprintf('Cannot resolve id "%s". No resolver is available.', substr($arg, 1)));
                }
            }
        }
    }

    protected function computeSourceKey($objectClass, $sourceId, $sourceClass, $sourceMethod)
    {
        if (null === $sourceId) {
            return $objectClass . $sourceClass . $sourceMethod;
        } 
        
        return $objectClass . $sourceId . $sourceClass . $sourceMethod;
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

    protected function setTemporaryValueForPropertyToLazyLoad($value, $object, $mappedFieldName)
    {
        //The property will be lazy loaded.
        //We just set the id of object to lazy load in the property.
        //We cannot use the setter to put this id because it cast on object type.
        //Later we will transform this id to the corresponding object.
        if (! $this->memberAccessStrategy instanceof MemberAccessStrategy\PropertyAccessStrategy) {
            $memberAccessStrategy = $this->createPropertyAccessStrategy($object);
        } else {
            $memberAccessStrategy = $this->memberAccessStrategy;
        }

        $memberAccessStrategy->setValue($value, $object, $mappedFieldName);
    }

    protected function doPrepare($object, ObjectKey $objectKey = null)
    {
        if (isset($object)) {

            $this->memberAccessStrategy = $this->createMemberAccessStrategy($object);
        }
    }

    private function createMemberAccessStrategy($object)
    {
        $memberAccessStrategy = 
        $this->isPropertyAccessStrategyOn ? 
        new MemberAccessStrategy\PropertyAccessStrategy : 
        new MemberAccessStrategy\GetterSetterAccessStrategy(new MemberAccessStrategy\PropertyAccessStrategy)
        ;
        
        $memberAccessStrategy->prepare($object, $this->metadata);

        return $memberAccessStrategy;
    }

    private function createPropertyAccessStrategy($object)
    {
        $memberAccessStrategy = new MemberAccessStrategy\PropertyAccessStrategy;
        $memberAccessStrategy->prepare($object, $this->metadata);

        return $memberAccessStrategy;
    }
}
