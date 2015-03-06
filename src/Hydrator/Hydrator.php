<?php

namespace Kassko\DataMapper\Hydrator;

use DateTime;
use Exception;
use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\Configuration\ObjectKey;
use Kassko\DataMapper\Configuration\RuntimeConfiguration;
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
        $methods = get_class_methods($object);
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

                /*if ($filter && !$filter->filter($mappedFieldName)) {
                    continue;
                }*/

                $value = $this->memberAccessStrategy->getValue($object, $mappedFieldName);
                $value = $this->extractValue($mappedFieldName, $value, $object, $data);

                $data[$originalFieldName] = $value;
            }
        } else {

            list($customHydratorClass, , $customExtractMethod) = $this->metadata->getCustomHydratorInfo();
            $customHydrator = $this->classResolver ? $this->classResolver->resolve($customHydratorClass) : new $customHydratorClass;
            $data = $customHydrator->$customExtractMethod($object);
        }

        //To one extraction.
        $toOneAssociations = $this->metadata->getSingleValuedAssociations();
        if (count($toOneAssociations) > 0) {

            $id = $data[$this->metadata->getIdFieldName()];
            foreach ($toOneAssociations as $mappedFieldName) {

                if ($this->metadata->isNotManaged($mappedFieldName)) {
                    continue;
                }

                $objectClass = $this->metadata->getAssociationTargetClass($mappedFieldName);
                $targetObject = $this->memberAccessStrategy->getValue($object, $mappedFieldName);
                $targetObjectMetadata = $this->objectManager->getMetadata($targetObjectClass = get_class($targetObject));

                if (null === $targetIdFieldName = $targetObjectMetadata->getIdFieldName()) {
                    throw new ObjectMappingException(sprintf("Cannot work with associations in object [%s]. This object have not identity defined in its metadata.", $targetObjectClass));
                }

                $targetObjectHydrator = $this->objectManager->createHydratorFor(get_class($targetObject));
                $targetData = $targetObjectHydrator->extract($targetObject);

                $data[$originalFieldName] = $targetData[$targetIdFieldName];
                $data[$this->getRelationFieldNameExtraction($originalFieldName)] = $targetData;
            }
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

                    //It's possible that a raw field name don't mathh a field name
                    //beacause a raw field can be a value object part.
                    continue;
                }

                if (! $this->metadata->hasAssociation($mappedFieldName)) {

                    //Classical hydration.
                    $this->walkHydration($mappedFieldName, $object, $value, $data);
                }
            }
        } else {

            list($customHydratorClass, $customHydrateMethod) = $this->metadata->getCustomHydratorInfo();
            $customHydrator = $this->classResolver ? $this->classResolver->resolve($customHydratorClass) : new $customHydratorClass;
            $customHydrator->$customHydrateMethod($data, $object);
        }

        //To one hydration.
        $toOneAssociations = $this->metadata->getSingleValuedAssociations();
        if (count($toOneAssociations) > 0) {

            $id = $data[$this->metadata->getIdFieldName()];
            foreach ($toOneAssociations as $mappedFieldName) {
                $this->walkToOneHydration(
                    $mappedFieldName,
                    $object,
                    $data[$this->metadata->getOriginalFieldName($mappedFieldName)],
                    false
                );
            }
        }

        //To many hydration.
        $toManyAssociations = $this->metadata->getCollectionValuedAssociations();
        if (count($toManyAssociations) > 0) {

            $id = $data[$this->metadata->getIdFieldName()];
            foreach ($toManyAssociations as $mappedFieldName) {
                $this->walkToManyHydration($mappedFieldName, $object, $id, false);
            }
        }

        //Provider hydration.
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
        
        if ($this->metadata->isSingleValuedAssociation($mappedFieldName)) {

            $this->walkToOneHydration($mappedFieldName, $object, $this->memberAccessStrategy->getValue($object, $mappedFieldName), true);
        } elseif ($this->metadata->isCollectionValuedAssociation($mappedFieldName)) {

            $id = $data[$this->metadata->getIdFieldName()];
            $this->walkToManyHydration($mappedFieldName, $object, $id, true);
        } elseif ($this->metadata->hasProvider($mappedFieldName)) {

            $this->walkHydrationByProvider($mappedFieldName, $object, true);
        } else {

            throw ObjectMappingException::notFoundAssociation($mappedFieldName, $this->metadata->getName());
        }
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

        $value = $this->hydrateValue($mappedFieldName, $value, $data, $object);

        //We do not call the setter for a DateTime when the value to defined is null or empty.
        //We do so instead of initialize this DateTime to the current date.
        if (empty($value) && $this->metadata->isMappedDateField($mappedFieldName)) {
            return true;
        }

        //if ($this->hasStrategy($mappedFieldName)) {
            $this->memberAccessStrategy->setScalarValue($value, $object, $mappedFieldName);
        //}

        return true;
    }

    protected function walkToOneHydration($mappedFieldName, $object, $value, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        if (! $this->hasStrategy($mappedFieldName)) {
            $value = $this->handleTypeConversions($value, $this->metadata->getTypeOfMappedField($mappedFieldName));
        }

        list($objectClass, $repositoryClass, $findMethod, $lazyLoading) = $this->metadata->getSingleValuedAssociationInfo($mappedFieldName);

        if (false === $enforceLoading && true === $lazyLoading) {

            $this->setTemporaryValueForPropertyToLazyLoad($value, $object, $mappedFieldName);
            return false;
        }

        $idFieldName = $this->metadata->getIdFieldName();
        if (! isset($idFieldName)) {
            throw new ObjectMappingException(sprintf("We cannot work with association with this object [%s]. This object have not an identity in its metadata.", $objectClass));
        }

        if ($this->metadata->isSingleValuedAssociation($mappedFieldName)) {

            $this->hydrateToOne($objectClass, $value, $mappedFieldName, $object, $findMethod, $repositoryClass);
        }

        return true;
    }

    protected function walkToManyHydration($mappedFieldName, $object, $value, $enforceLoading)
    {
        list($associationName, $objectClass, $repositoryClass, $findMethod, $lazyLoading) = $this->metadata->getCollectionValuedAssociationInfo($mappedFieldName);

        if (false === $enforceLoading && true === $lazyLoading) {

            $this->setTemporaryValueForPropertyToLazyLoad($value, $object, $mappedFieldName);
            return false;
        }

        $this->hydrateToMany($objectClass, $id, $mappedFieldName, $object, $findMethod, $repositoryClass, $associationName);

        return true;
    }

    protected function walkHydrationByProvider($mappedFieldName, $object, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        list($class, $method, $lazyLoading) = $this->metadata->getProvidersInfo($mappedFieldName);
        $key = $class.$method;

        if (! isset($this->providerLoadingDone[$key]) && ($enforceLoading || ! $lazyLoading)) {

            $this->findFromProviders($class, $method, $object);
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
        $this->memberAccessStrategy->setScalarValue($valueObject, $object, $mappedFieldName);
        $result = $valueObjectHydrator->hydrate($data, $valueObject, $objectKey);
        $this->popRuntimeConfiguration();

        return $result;
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

    protected function hydrateToOne($objectClass, $id, $mappedFieldName, $object, $findMethod, $repositoryClass)
    {
        $subObject = $this->find($objectClass, $id, $findMethod, $repositoryClass) ?: new $objectClass;
        $this->memberAccessStrategy->setSingleAssociation($subObject, $object, $mappedFieldName);
    }

    protected function hydrateToMany($objectClass, $id, $mappedFieldName, $object, $findMethod, $repositoryClass, $associationName)
    {
        $subObjects = $this->findCollection($objectClass, $id, $findMethod, $repositoryClass) ?: [];
        $this->memberAccessStrategy->setCollectionAssociation($subObjects, $object, $mappedFieldName, $associationName);
    }

    /**
     * Find an object from a FQCN and an identity.
     *
     * @param string $objectClass FQCN of object to find.
     * @param mixed $id Identity of object to find.
     * @param mixed $findMethod Method witch find object.
     *
     * @return object|null Return the object or null if it's not found.
     */
    protected function find($objectClass, $id, $findMethod, $repositoryClass)
    {
        return $this->objectManager->find($objectClass, $id, $findMethod, $repositoryClass);
    }

    /**
     * Find a collection from a FQCN.
     *
     * @param string $objectClass FQCN of object to find.
     * @param mixed $findMethod Method witch find collection.
     *
     * @return object|null Renvoi the collection or null if it's not found.
     */
    protected function findCollection($objectClass, $findMethod, $repositoryClass)
    {
        return $this->objectManager->findCollection($objectClass, $findMethod, $repositoryClass);
    }

    protected function findFromProviders($customSourceClass, $customSourceMethod, $object)
    {
        $this->objectManager->findFromProviders($customSourceClass, $customSourceMethod, $object);
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

        $memberAccessStrategy->setScalarValue($value, $object, $mappedFieldName);
    }

    protected function doPrepare($object, ObjectKey $objectKey = null)
    {
        if (isset($object)) {

            $this->memberAccessStrategy = $this->createMemberAccessStrategy($object);
        }
    }

    private function createMemberAccessStrategy($object)
    {
        $memberAccessStrategy = $this->isPropertyAccessStrategyOn ? new MemberAccessStrategy\PropertyAccessStrategy : new MemberAccessStrategy\GetterSetterAccessStrategy;
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
