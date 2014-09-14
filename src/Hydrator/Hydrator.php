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
     * Permet le suivi des propriétés déjà hydratés. Uniquement les propriétés hydratés par des sources externes.
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
	* Extrait les données d'un objet selon une logique d'accès à ses membres (par les getters/setters ou directement par les propriétés).
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
                        throw new ObjectMappingException(sprintf("Les relations ne sont pas gérées pour l'objet [%s]. Cet objet n'a pas de métadonnée d'identité.", $targetObjectClass));
                    }

                    $targetObjectHydrator = $this->objectManager->createHydratorFor(get_class($targetObject));
                    $targetData = $targetObjectHydrator->extract($targetObject);

                    $data[$originalFieldName] = $targetData[$targetIdFieldName];
                    $data[$this->getRelationFieldNameExtraction($originalFieldName)] = $targetData;
                } //elseif ($this->metadata->isCollectionValuedAssociation($mappedFieldName)) {

                    //Kassko Only the use case of toOne association is handled !
                //}
            } else {

                $value = $this->memberAccessStrategy->getValue($object, $mappedFieldName);
                $value = $this->extractValue($mappedFieldName, $value, $object, $data);//Faut-il vraiment transmettre un $data en transition ???

                $data[$originalFieldName] = $value;
            }
        }

        return $data;
    }

    /**
	* Extrait les données d'un objet selon une logique d'accès à ses membres (par les getters/setters ou directement par les propriétés).
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

                $this->walkToOneHydration($mappedFieldName, $object, $data[$this->metadata->getOriginalFieldName($mappedFieldName)], false);
            } else {

                $this->walkHydration($mappedFieldName, $object, $value, $data);
            }
        }

        foreach ($this->metadata->getCollectionValuedAssociations() as $mappedFieldName) {

            $this->walkToManyHydration($mappedFieldName, $object, false);
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

        //On appelle pas le setter d'un DateTime quand la valeur à définir est nulle ou vide.
        //On fait ainsi plutôt que d'initialiser ce Datetime par défaut à la date du jour.
        if (empty($value) && $this->metadata->isMappedDateField($mappedFieldName)) {
            return true;
        }

        //if ($this->hasStrategy($mappedFieldName)) {
            $this->memberAccessStrategy->setScalarValue($value, $object, $mappedFieldName);
        //}

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

            //On force à définir la propriété directement.
            //Comme on fait du lazy loading, on ne stocke pas l'objet hydraté mais juste son id.
            //On ne peut pas définir la propriété à cet id par le biais du setter car le setter caste sur le type de l'objet attendu.
            if (! $this->memberAccessStrategy instanceof MemberAccessStrategy\PropertyAccessStrategy) {
                $memberAccessStrategy = $this->createPropertyAccessStrategy($object);
            } else {
                $memberAccessStrategy = $this->memberAccessStrategy;
            }

            $memberAccessStrategy->setScalarValue($value, $object, $mappedFieldName);

            return false;
        }

        $idFieldName = $this->metadata->getIdFieldName();
        if (! isset($idFieldName)) {
            throw new ObjectMappingException(sprintf("Les relations ne sont pas gérées pour l'objet [%s]. Cet objet n'a pas de métadonnée d'identité.", $objectClass));
        }

        if ($this->metadata->isSingleValuedAssociation($mappedFieldName)) {

            $this->hydrateToOne(
                $objectClass,
                $value,
                $mappedFieldName,
                $object,//Ce n'est pas idFieldName que l'on veut, mais l'id de l'entité en relation.
                $findMethod,
                $repositoryClass
            );
        }

        return true;
    }

    protected function walkValueObjectHydration($mappedFieldName, $object, $data)
    {
        if ($this->metadata->isNotManaged($mappedFieldName)) {
            return false;
        }

        $valueObjectHydrator = $this->objectManager->createHydratorFor(get_class($object));

        return $valueObjectHydrator->hydrate($data);
    }

    protected function walkToManyHydration($mappedFieldName, $object, $enforceLoading)
    {
        list($associationName, $objectClass, $repositoryClass, $findMethod, $lazyLoading) = $this->metadata->getCollectionValuedAssociationInfo($mappedFieldName);

        if (false === $enforceLoading && true === $lazyLoading) {
            return false;
        }

        $this->hydrateToMany($objectClass, $mappedFieldName, $object, $findMethod, $repositoryClass, $associationName);

        return true;
    }

    protected function hydrateToOne($objectClass, $id, $mappedFieldName, $object, $findMethod, $repositoryClass)
    {
        $subObject = $this->find($objectClass, $id, $findMethod, $repositoryClass) ?: new $objectClass;
        $this->memberAccessStrategy->setSingleAssociation($subObject, $object, $mappedFieldName);
    }

    protected function hydrateToMany($objectClass, $mappedFieldName, $object, $findMethod, $repositoryClass, $associationName)
    {
        $subObjects = $this->findCollection($objectClass, $findMethod, $repositoryClass) ?: [];
        $this->memberAccessStrategy->setCollectionAssociation($subObjects, $object, $mappedFieldName, $associationName);
    }

    /**
     * Trouve un objet à partir d'un nom de classe et d'une identité.
     *
     * @param string $objectClass Classe de l'objet à trouver.
     * @param mixed $id Identité de l'objet à trouver.
     * @param mixed $findMethod Méthode permettant de récupérer l'objet.
     *
     * @return object|null Renvoi l'objet ou null s'il n'est pas trouvé.
     */
    protected function find($objectClass, $id, $findMethod, $repositoryClass)
    {
        return $this->objectManager->find($objectClass, $id, $findMethod, $repositoryClass);
    }

    /**
     * Trouve une collection d'objets à partir d'un nom de classe.
     *
     * @param string $objectClass Classe de l'objet à trouver.
     * @param mixed $findMethod Méthode permettant de récupérer la collection d'objets.
     *
     * @return object|null Renvoi l'objet ou null s'il n'est pas trouvé.
     */
    protected function findCollection($objectClass, $findMethod, $repositoryClass)
    {
        return $this->objectManager->findCollection($objectClass, $findMethod, $repositoryClass);
    }

    protected function findFromCustomHydrationSource($customSourceClass, $customSourceMethod, $object)
    {
        $this->objectManager->findFromCustomHydrationSource($customSourceClass, $customSourceMethod, $object);
    }

    /**
     * Trouve un objet à partir d'un nom de classe et d'une identité.
     *
     * @param string $objectClass Classe de l'objet à trouver.
     * @param mixed $id Identité de l'objet à trouver.
     *
     * @return object|null Renvoi l'objet ou null s'il n'est pas trouvé.
     */
    /*
    protected function findByCallable($objectClass, $finderName)
    {
        return $this->objectManager->findByCallable($objectClass, $finderName);
    }
    */

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