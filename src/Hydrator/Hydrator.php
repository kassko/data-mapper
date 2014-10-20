<?php

namespace Kassko\DataAccess\Hydrator;

use Kassko\DataAccess\Exception\ObjectMappingException;
use Kassko\DataAccess\Hydrator\MemberAccessStrategy;
use Kassko\DataAccess\ObjectManager;
use DateTime;
use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;
use Exception;

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
    * @var \Kassko\DataAccess\Hydrator\MemberAccessStrategy\MemberAccessStrategyInterface
    */
    protected $memberAccessStrategy;

    /**
     * Track properties already hydrated. Only properties hydrated by custom sources.
     */
    private $customHydrationSourceDone;


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

	/**
	* {@inheritdoc}
	*/
    public function extract($object)
    {
        $this->prepare($object);
        return $this->doExtract($object);
    }

    /**
	* {@inheritdoc}
	*/
    public function hydrate(array $data, $object)
    {
        $this->prepare($object);
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
        foreach ($originalFieldNames as $originalFieldName) {
        	$mappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);

            if ($this->metadata->isNotManaged($mappedFieldName)) {
                continue;
            }

            /*if ($filter && !$filter->filter($mappedFieldName)) {
                continue;
            }*/

            if ($this->metadata->isValueObject($mappedFieldName)) {

                $valueObjectHydrator = $this->objectManager->createHydratorFor(get_class($object));
                return $valueObjectHydrator->extract($object);
            } elseif ($this->metadata->hasAssociation($mappedFieldName)) {

                $objectClass = $this->metadata->getAssociationTargetClass($mappedFieldName);

                if ($this->metadata->isSingleValuedAssociation($mappedFieldName)) {

                    $targetObject = $this->memberAccessStrategy->getValue($object, $mappedFieldName);
                    $targetObjectMetadata = $this->objectManager->getMetadata($targetObjectClass = get_class($targetObject));

                    if (null === $targetIdFieldName = $targetObjectMetadata->getIdFieldName()) {
                        throw new ObjectMappingException(sprintf("Cannot work with associations with object [%s]. This object have not identity defined in its metadata.", $targetObjectClass));
                    }

                    $targetObjectHydrator = $this->objectManager->createHydratorFor(get_class($targetObject));
                    $targetData = $targetObjectHydrator->extract($targetObject);

                    $data[$originalFieldName] = $targetData[$targetIdFieldName];
                    $data[$this->getRelationFieldNameExtraction($originalFieldName)] = $targetData;
                } //elseif ($this->metadata->isCollectionValuedAssociation($mappedFieldName)) {

                    //Kassko Todo actually only the use case of toOne association is handled !
                //}
            } else {

                $value = $this->memberAccessStrategy->getValue($object, $mappedFieldName);
                $value = $this->extractValue($mappedFieldName, $value, $object, $data);

                $data[$originalFieldName] = $value;
            }
        }

        return $data;
    }

    /**
	* Extract data from an object.
	*
	* @param array $data
	* @param object $object
	* @throws RuntimeException
	* @return object
	*/
    protected function doHydrate(array $data, $object)
    {
        foreach ($data as $originalFieldName => $value) {
        	$mappedFieldName = $this->metadata->getMappedFieldName($originalFieldName);

            if ($this->metadata->isValueObject($mappedFieldName)) {

                $this->walkValueObjectHydration($mappedFieldName, $object, $data);
            } elseif ($this->metadata->hasAssociation($mappedFieldName)) {

                $this->walkToOneHydration(
                    $mappedFieldName,
                    $object,
                    $data[$this->metadata->getOriginalFieldName($mappedFieldName)],
                    false
                );
            } else {

                $this->walkHydration($mappedFieldName, $object, $value, $data);
            }
        }

        $id = $data[$this->metadata->getIdFieldName()];
        foreach ($this->metadata->getCollectionValuedAssociations() as $mappedFieldName) {

            $this->walkToManyHydration($mappedFieldName, $object, $id, false);
        }

        $this->customHydrationSourceDone = [];
        foreach ($this->metadata->getFieldsWithCustomHydrationSource() as $mappedFieldName) {

            if ($this->metadata->hasCustomHydrationSource($mappedFieldName)) {
                $this->walkHydrationByCustomSource($mappedFieldName, $object, false);
            }
        }

        return $object;
    }

    public function loadProperty($object, $mappedFieldName)
    {
        if ($this->metadata->hasCustomHydrationSource($mappedFieldName)) {

            $this->walkHydrationByCustomSource($mappedFieldName, $object, true);
        } elseif ($this->metadata->isSingleValuedAssociation($mappedFieldName)) {

            $this->walkToOneHydration($mappedFieldName, $object, $this->memberAccessStrategy->getValue($object, $mappedFieldName), true);
        } elseif ($this->metadata->isCollectionValuedAssociation($mappedFieldName)) {

            $this->walkToManyHydration($mappedFieldName, $object, true);
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

            $this->setTemporaryValueForPropertyToLazyLoad($id, $object, $mappedFieldName);
            return false;
        }

        $this->hydrateToMany($objectClass, $id, $mappedFieldName, $object, $findMethod, $repositoryClass, $associationName);

        return true;
    }

    protected function walkHydrationByCustomSource($mappedFieldName, $object, $enforceLoading)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        list($class, $method, $lazyLoading) = $this->metadata->getCustomHydrationSourceInfo($mappedFieldName);
        $key = $class.$method;

        if (! isset($this->customHydrationSourceDone[$key]) && ($enforceLoading || ! $lazyLoading)) {

            $this->findFromCustomHydrationSource($class, $method, $object);
            $this->customHydrationSourceDone[$key] = true;
        }
    }

    protected function walkValueObjectHydration($mappedFieldName, $object, $data)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        $valueObjectHydrator = $this->objectManager->createHydratorFor(get_class($object));

        return $valueObjectHydrator->hydrate($data);
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

    protected function findFromCustomHydrationSource($customSourceClass, $customSourceMethod, $object)
    {
        $this->objectManager->findFromCustomHydrationSource($customSourceClass, $customSourceMethod, $object);
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

    protected function doPrepare($object)
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