<?php

namespace Kassko\DataAccess\ClassMetadata;

use Kassko\DataAccess\Exception\ObjectMappingException;
use Kassko\DataAccess\Hydrator\Hydrator;

/**
* Hold class metadata.
*
* @author kko
*/
class ClassMetadata
{
    const INDEX_EXTRACTION_STRATEGY = 0;
    const INDEX_HYDRATION_STRATEGY = 1;
    const INDEX_METADATA_EXTENSION_CLASS = 2;

    private $originalFieldNames = [];
    private $mappedFieldNames = [];
    private $mappedDateFieldNames = [];
    private $mappedIdFieldName;
    private $mappedIdCompositePartFieldName = [];
    private $mappedVersionFieldName;
    private $toOriginal = [];
    private $toMapped = [];
    private $fieldsDataByKey = [];
    private $columnDataName = 'column';
    private $valueObjectsByKey = [];
    private $repositoryClass;
    private $objectReadDateFormat;
    private $objectWriteDateFormat;
    //private $phpMetadataClass;
    private $propertyAccessStrategyEnabled;

    /**
     * @var string Fqcn de la classes contenant les métadonnées de type "callback"
     */
    private $metadataExtensionClass;

    private $valueObjectsClassNames = [];
    private $valueObjectsMetadata = [];
    private $mappedManagedFieldNames = [];
    private $mappedTransientFieldNames = [];
    private $fieldsWithHydrationStrategy = [];
    private $toOneAssociations = [];
    private $toManyAssociations = [];

    /**
     * Source (class and method) which hydrate a field.
     * @var array
     */
    private $customHydrationSource = [];
    private $objectListenerClasses = [];
    private $idGetter;
    private $idSetter;
    private $versionGetter;
    private $versionSetter;

    private $onBeforeExtract;
    private $onAfterExtract;
    private $onBeforeHydrate;
    private $onAfterHydrate;

    /**
     * @var ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @param object|string $objectName
     */
    public function __construct($objectName)
    {
        $this->reflectionClass = new \ReflectionClass($objectName);
    }

    /**
    * Gets the fully-qualified class name of this persistent class.
    *
    * @return string
    */
    public function getName()
    {
        return $this->reflectionClass->getName();
    }



    /**
    * Gets the ReflectionClass instance for this mapped class.
    *
    * @return \ReflectionClass
    */
    public function getReflectionClass()
    {
        return $this->reflectionClass;
    }

    public function compile()
    {
        //Pour chaque champs, on injecte les réglages hérités si aucun réglage n'est défini au niveau du champs considéré.
        //Cela évitera de résoudre à chaque fois la valeurs de l'options, en regardant d'abord au niveau du chammps puis au niveau de l'objet.
        //On regardera tout le temps au niveau du champs.

        //VOIR POUR EVITER DE FAIRE DES ISSET, TRAVAILLER A AVOIR EN PERMANENCE TOUTES LES CLES MAIS INITIALISEES A NULL PAR DEFAUT
        //PUIS ENSUITE, RETIRER TOUS lES ISSET ACTUELLEMENT FAITS.

        if (isset($this->objectReadDateFormat) || isset($this->objectWriteDateFormat)) {

            foreach ($this->fieldsDataByKey as $fieldName => &$fieldDataByKey) {

                if (isset($fieldDataByKey['column']['type']) && 'date' == $fieldDataByKey['column']['type']) {

                    if (isset($this->objectReadDateFormat)) {
                        $fieldDataByKey['column']['readDateFormat'] = $this->objectReadDateFormat;
                    }

                    if (isset($this->objectWriteDateFormat)) {
                        $fieldDataByKey['column']['writeDateFormat'] = $this->objectWriteDateFormat;
                    }
                }
            }

            unset($fieldDataByKey);//Par précaution !
        }
    }

    public function getToOneAssociations()
    {
        return $this->toOneAssociations;
    }

    public function setToOneAssociations(array $toOneAssociations)
    {
        $this->toOneAssociations = $toOneAssociations;

        return $this;
    }

    public function getToManyAssociations()
    {
        return $this->toManyAssociations;
    }

    public function setToManyAssociations(array $toManyAssociations)
    {
        $this->toManyAssociations = $toManyAssociations;

        return $this;
    }

    public function hasAssociation($mappedFieldName)
    {
        return
            array_key_exists($mappedFieldName, $this->toOneAssociations)
            ||
            array_key_exists($mappedFieldName, $this->toManyAssociations)
        ;
    }

    public function getAssociationTargetClass($mappedFieldName)
    {
        if (array_key_exists($mappedFieldName, $this->toOneAssociations)) {

            if (isset($this->toOneAssociations[$mappedFieldName]['entityClass'])) {

                return $this->toOneAssociations[$mappedFieldName]['entityClass'];
            }
        }


        if (array_key_exists($mappedFieldName, $this->toManyAssociations)) {

            if (isset($this->toManyAssociations[$mappedFieldName]['entityClass'])) {

                return $this->toManyAssociations[$mappedFieldName]['entityClass'];
            }
        }


        throw ObjectMappingException::notFoundAssociationTargetClass($mappedFieldName, $this->reflectionClass->getName());
    }

    public function getSingleValuedAssociationInfo($mappedFieldName)
    {
        if (! array_key_exists($mappedFieldName, $this->toOneAssociations)) {
            throw ObjectMappingException::notFoundAssociation($mappedFieldName, $this->reflectionClass->getName());
        }

        if (
            ! isset($this->toOneAssociations[$mappedFieldName]['entityClass'])
            ||
            ! isset($this->toOneAssociations[$mappedFieldName]['findMethod'])
            ||
            ! isset($this->toOneAssociations[$mappedFieldName]['lazyLoading'])
        ) {
            throw ObjectMappingException::notFoundAssociationInfo($mappedFieldName, $this->reflectionClass->getName());
        }

        return [
            $this->toOneAssociations[$mappedFieldName]['entityClass'],
            isset($this->toOneAssociations[$mappedFieldName]['repositoryClass']) ? $this->toOneAssociations[$mappedFieldName]['repositoryClass'] : null,
            $this->toOneAssociations[$mappedFieldName]['findMethod'],
            $this->toOneAssociations[$mappedFieldName]['lazyLoading'],
        ];
    }

    public function getCollectionValuedAssociationInfo($mappedFieldName)
    {
        if (! array_key_exists($mappedFieldName, $this->toManyAssociations)) {
            throw ObjectMappingException::notFoundAssociation($mappedFieldName, $this->reflectionClass->getName());
        }

        $infoKeys = ['name', 'entityClass', 'findMethod', 'lazyLoading'];
        foreach ($infoKeys as $infoKey) {

            if (! isset($this->toManyAssociations[$mappedFieldName][$infoKey])) {

                throw ObjectMappingException::notFoundAssociationInfo($mappedFieldName, $this->reflectionClass->getName(), $infoKey);
            }
        }

        return [
            $this->toManyAssociations[$mappedFieldName]['name'],
            $this->toManyAssociations[$mappedFieldName]['entityClass'],
            isset($this->toManyAssociations[$mappedFieldName]['repositoryClass']) ? $this->toManyAssociations[$mappedFieldName]['repositoryClass'] : null,
            $this->toManyAssociations[$mappedFieldName]['findMethod'],
            $this->toManyAssociations[$mappedFieldName]['lazyLoading'],
        ];
    }

    /*
    public function getAssociationFindMethod($mappedFieldName)
    {
        if (array_key_exists($mappedFieldName, $this->toOneAssociations)) {

            if (isset($this->toOneAssociations[$mappedFieldName]['findMethod'])) {

                return $this->toOneAssociations[$mappedFieldName]['findMethod'];
            }
        }


        if (array_key_exists($mappedFieldName, $this->toManyAssociations)) {

            if (isset($this->toManyAssociations[$mappedFieldName]['entityClass'])) {

                return $this->toManyAssociations[$mappedFieldName]['entityClass'];
            }
        }


        throw ObjectMappingException::notFoundAssociationFindMethod($mappedFieldName, $this->reflectionClass->getName());
    }
    */

    public function isSingleValuedAssociation($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->toOneAssociations);
    }

    public function isCollectionValuedAssociation($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->toManyAssociations);
    }

    public function getCollectionValuedAssociations()
    {
        return array_keys($this->toManyAssociations);
    }

    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;
        return $this;
    }

    public function getObjectReadDateFormat()
    {
        return $this->objectReadDateFormat;
    }

    public function setObjectReadDateFormat($objectReadDateFormat)
    {
        $this->objectReadDateFormat = $objectReadDateFormat;
        return $this;
    }

    public function getObjectWriteDateFormat()
    {
        return $this->objectWriteDateFormat;
    }

    public function setObjectWriteDateFormat($objectWriteDateFormat)
    {
        $this->objectWriteDateFormat = $objectWriteDateFormat;
        return $this;
    }

    /*
    public function getPhpMetadataClass()
    {
        return $this->phpMetadataClass;
    }

    public function setPhpMetadataClass($phpMetadataClass)
    {
        $this->phpMetadataClass = $phpMetadataClass;
        return $this;
    }
    */

    public function setOriginalFieldNames(array $fieldNames)
    {
        $this->originalFieldNames = $fieldNames;
        return $this;
    }

    public function getMappedDateFieldNames()
    {
        return $this->mappedDateFieldNames;
    }

    public function setMappedDateFieldNames(array $mappedDateFieldNames)
    {
        $this->mappedDateFieldNames = $mappedDateFieldNames;
        return $this;
    }

    public function getMappedFieldNames()
    {
        return $this->mappedFieldNames;
    }

    public function setMappedFieldNames(array $mappedFieldNames)
    {
        $this->mappedFieldNames = $mappedFieldNames;
        return $this;
    }

    public function getFieldsDataByKey()
    {
        return $this->fieldsDataByKey;
    }

    public function setFieldsDataByKey(array $fieldDataByKey)
    {
        $this->fieldsDataByKey = $fieldDataByKey;
        return $this;
    }

    public function getMappedIdFieldName()
    {
        return $this->mappedIdFieldName;
    }

    public function setMappedIdFieldName($mappedIdFieldName)
    {
        $this->mappedIdFieldName = $mappedIdFieldName;
        $this->idGetter = self::getterise($this->mappedIdFieldName);
        $this->idSetter = self::setterise($this->mappedIdFieldName);

        return $this;
    }

    public function getMappedIdCompositePartFieldName()
    {
        return $this->mappedIdCompositePartFieldName;
    }

    public function setMappedIdCompositePartFieldName(array $mappedIdCompositePartFieldName)
    {
        $this->mappedIdCompositePartFieldName = $mappedIdCompositePartFieldName;
        return $this;
    }

    public function getMappedVersionFieldName()
    {
        return $this->mappedVersionFieldName;
    }

    public function setMappedVersionFieldName($mappedVersionFieldName)
    {
        $this->mappedVersionFieldName = $mappedVersionFieldName;
        $this->versionGetter = self::getterise($this->mappedVersionFieldName);
        $this->versionSetter = self::setterise($this->mappedVersionFieldName);

        return $this;
    }

    public function getToOriginal()
    {
        return $this->toOriginal;
    }

    public function setToOriginal(array $toOriginal)
    {
        $this->toOriginal = $toOriginal;
        return $this;
    }

    public function getToMapped()
    {
        return $this->toMapped;
    }

    public function setToMapped(array $toMapped)
    {
        $this->toMapped = $toMapped;
        return $this;
    }

    /*public function getColumnAnnotationName()
    {
        return $this->columnDataName;
    }

    public function setColumnAnnotationName($columnDataName)
    {
        $this->columnDataName = $columnDataName;
        return $this;
    }*/

    public function isPropertyAccessStrategyEnabled()
    {
        return $this->propertyAccessStrategyEnabled;
    }

    public function setPropertyAccessStrategyEnabled($propertyAccessStrategyEnabled)
    {
        $this->propertyAccessStrategyEnabled = $propertyAccessStrategyEnabled;
        return $this;
    }

    /**
     * Gets the value of metadataExtensionClass for a given fieldName.
     *
     * @var mappedFieldName string Nom d'un champs pour lequel on cherche la classe contenant ses métadonnées de callback.
     *
     * @return string Fqcn de la classes contenant les métadonnées de type "callback" pour un champs donné
     */
    public function getMetadataExtensionClassByMappedField($mappedFieldName)
    {
        //$prefix = '\\';
        $prefix = '';

        if (null != $data = $this->getDataForField($mappedFieldName, $this->columnDataName)) {

            switch (true) {

                case isset($data['metadataExtensionClass']):
                    return $prefix.$data['metadataExtensionClass'];

                case isset($this->metadataExtensionClass):
                    return $prefix.$this->metadataExtensionClass;
            }
        }

        return null;
    }

    /**
     * Gets the value of metadataExtensionClass.
     *
     * @return string Fqcn de la classes contenant les métadonnées de type "callback"
     */
    public function getMetadataExtensionClass()
    {
        return $this->metadataExtensionClass;
    }

    /**
     * Sets the value of metadataExtensionClass.
     *
     * @param string Fqcn de la classes contenant les métadonnées de type "callback" $metadataExtensionClass the metadata extension class
     *
     * @return self
     */
    public function setMetadataExtensionClass($metadataExtensionClass)
    {
        $this->metadataExtensionClass = $metadataExtensionClass;

        return $this;
    }

    public function isValueObject($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->valueObjectsByKey);
    }

    public function getValueObjectsByKey()
    {
        return $this->valueObjectsByKey;
    }

    public function setValueObjectsByKey(array $valueObjectsByKey)
    {
        $this->valueObjectsByKey = $valueObjectsByKey;
        return $this;
    }

    public function getValueObjectsClassNames()
    {
        return $this->valueObjectsClassNames;
    }

    public function setValueObjectsClassNames(array $valueObjectsClassNames)
    {
        $this->valueObjectsClassNames = $valueObjectsClassNames;
        return $this;
    }

    public function getValueObjectsMetadata()
    {
        return $this->valueObjectsMetadata;
    }

    public function setValueObjectsMetadata(array $valueObjectsMetadata)
    {
        $this->valueObjectsMetadata = $valueObjectsMetadata;
        return $this;
    }

    public function isTransient($mappedFieldName)
    {
        return in_array($mappedFieldName, $this->mappedTransientFieldNames);
    }

    public function setMappedTransientFieldNames(array $mappedTransientFieldNames)
    {
        $this->mappedTransientFieldNames = $mappedTransientFieldNames;
        return $this;
    }

    public function isNotManaged($mappedFieldName)
    {
        return !in_array($mappedFieldName, $this->mappedManagedFieldNames);
    }

    public function setMappedManagedFieldNames(array $mappedManagedFieldNames)
    {
        $this->mappedManagedFieldNames = $mappedManagedFieldNames;
        return $this;
    }

    public function getOnBeforeExtract()
    {
        return $this->onBeforeExtract;
    }

    public function setOnBeforeExtract($onBeforeExtract)
    {
        $this->onBeforeExtract = $onBeforeExtract;
        return $this;
    }

    public function getOnAfterExtract()
    {
        return $this->onAfterExtract;
    }

    public function setOnAfterExtract($onAfterExtract)
    {
        $this->onAfterExtract = $onAfterExtract;
        return $this;
    }

    public function getOnBeforeHydrate()
    {
        return $this->onBeforeHydrate;
    }

    public function setOnBeforeHydrate($onBeforeHydrate)
    {
        $this->onBeforeHydrate = $onBeforeHydrate;
        return $this;
    }

    public function getOnAfterHydrate()
    {
        return $this->onAfterHydrate;
    }

    public function setOnAfterHydrate($onAfterHydrate)
    {
        $this->onAfterHydrate = $onAfterHydrate;
        return $this;
    }

    /*public function getHydrationStrategyByField($mappedFieldName)
    {
        if (array_key_exists($mappedFieldName, $this->fieldsWithHydrationStrategy)) {
            return $this->fieldsWithHydrationStrategy[$mappedFieldName];
        }

        return null;
    }*/

    public function getFieldsWithHydrationStrategy()
    {
        return $this->fieldsWithHydrationStrategy;
    }

    //Kassko: TO REVIEW.
    public function setFieldsWithHydrationStrategy(array $fieldsWithHydrationStrategy)
    {
        $this->fieldsWithHydrationStrategy = $fieldsWithHydrationStrategy;

        return $this;
    }

    //VOIR pour ne pas réevaluer à chaque fois les champs sans stratégie d'hydratation.
    public function computeFieldsWithHydrationStrategy()
    {
        $fieldsWithHydrationStrategy = [];

        foreach ($this->fieldsWithHydrationStrategy as $mappedFieldName => $fieldStrategy) {

            $objectExtension = null;
            $objectExtensionClass = $this->getMetadataExtensionClassByMappedField($mappedFieldName);
            if (null !== $objectExtensionClass) {

                $refl = new \ReflectionClass($objectExtensionClass);
                $objectExtension = $refl->newInstanceArgs();
            }

            $index = self::INDEX_HYDRATION_STRATEGY;
            $strategy = $fieldStrategy[$index];

            if (null === $objectExtensionClass) {

                $fieldStrategy[$index] = function ($valueContext, $context) use ($strategy) {

                    return $this->$strategy($valueContext, $context);
                };
            } else {

                $reflection = new \ReflectionClass($objectExtensionClass);
                $closure = $reflection->getMethod($strategy)->getClosure(new $objectExtensionClass);

                $fieldStrategy[$index] = function ($valueContext, $context) use ($strategy, $closure) {

                    return $closure($valueContext, $context);
                };
            }


            $index = self::INDEX_EXTRACTION_STRATEGY;
            $strategy = $fieldStrategy[$index];

            if (null === $objectExtensionClass) {

                $fieldStrategy[$index] = function ($valueContext, $context) use ($strategy) {

                    return $this->$strategy($valueContext, $context);
                };
            } else {

                $reflection = new \ReflectionClass($objectExtensionClass);
                $closure = $reflection->getMethod($strategy)->getClosure(new $objectExtensionClass);

                $strategy = $fieldStrategy[$index];
                $fieldStrategy[$index] = function ($valueContext, $context) use ($strategy, $closure) {

                    return $closure($valueContext, $context);
                };

            }

            $fieldsWithHydrationStrategy[$mappedFieldName] = $fieldStrategy;
        }

        return $fieldsWithHydrationStrategy;
    }

    public function getObjectListenerClasses()
    {
        return $this->objectListenerClasses;
    }

    public function setObjectListenerClasses(array $objectListenerClasses)
    {
        $this->objectListenerClasses = $objectListenerClasses;
        return $this;
    }

    /*
    public function findFieldWithAnnotation($annotationName, $default=null)
    {
        foreach ($this->fieldsDataByKey as $fieldName => $annotations) {
            if (array_key_exists($annotationName, $annotations)) {
                return $fieldName;
            }
        }

        return $default;
    }
    */

    public function getOriginalFieldNames()
    {
        return $this->originalFieldNames;
    }

    public function getTypeOfMappedField($mappedFieldName)
    {
        if (null != $data = $this->getDataForField($mappedFieldName, $this->columnDataName)) {
            return $data['type'];//<=== TO REVIEW !!! Kassko
        }

        return 'string';
    }

    public function getOriginalFieldName($mappedFieldName)
    {
        if (! isset($this->toOriginal[$mappedFieldName])) {
            return $mappedFieldName;
            //throw ObjectMappingException::originalFieldNameNotFound($mappedFieldName);
        }

        return $this->toOriginal[$mappedFieldName];
    }

    public function getMappedFieldName($originalFieldName)
    {
        if (! isset($this->toMapped[$originalFieldName])) {
            return $originalFieldName;
            //throw ObjectMappingException::mappedFieldNameNotFound($originalFieldName);
        }

        return $this->toMapped[$originalFieldName];
    }

    /*
    public function getOriginalsDateFieldNames()
    {
        return array_map(
            function ($dateMappedFieldName) {
                return $this->getOriginalFieldName($dateMappedFieldName);
            },
            $this->mappedDateFieldNames
        );
    }
    */

    public function mergeValueObjectMetadata($valueObjectClassName, ClassMetadata $valueObjectMetadata)
    {
        $this->valueObjectsMetadata[$valueObjectClassName] = $valueObjectMetadata;
        /*
        $this->mappedDateFieldNames = array_merge(
            $this->mappedDateFieldNames,
            $objectMetadata->getMappedDateFieldNames()
        );
        */
    }

    public function isMappedDateField($mappedFieldName)
    {
        return in_array($mappedFieldName, $this->mappedDateFieldNames);
    }

    public function isMappedFieldWithStrategy($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->fieldsWithHydrationStrategy);
    }

    public function getReadDateFormatByMappedField($mappedFieldName, $default)
    {//Kassko VOIR POUR EVITER LE ISSET, AVOIR EN PERMANENCE TOUTES LES CLES MAIS INITIALISEES A NULL PAR DEFAUT
        if (null != $data = $this->getDataForField($mappedFieldName, $this->columnDataName)) {
            return isset($data['readDateFormat']) ? $data['readDateFormat'] : $this->objectReadDateFormat;//<=== A REVOIR !!! Kassko
        }

        return $default;
    }

    public function getWriteDateFormatByMappedField($mappedFieldName, $default)
    {
        if (null != $data = $this->getDataForField($mappedFieldName, $this->columnDataName)) {
            return isset($data['writeDateFormat']) ? $data['writeDateFormat'] : $this->objectWriteDateFormat;//<=== A REVOIR !!! Kassko
        }

        return $default;
    }

    public function eventsExist()
    {
        return
            isset($this->onBeforeExtract)
            || isset($this->onAfterExtract)
            || isset($this->onBeforeHydrate)
            || isset($this->onAfterHydrate)
        ;
    }

    public function hasId()
    {
        return isset($this->mappedIdFieldName);
    }

    public function hasIdComposite()
    {
        return isset($this->mappedIdCompositePartFieldName);
    }

    public function isVersionned()
    {
        return isset($this->mappedVersionFieldName);
    }

    public function getIdFieldName()
    {
        return $this->getOriginalFieldName($this->mappedIdFieldName);
    }

    public function getVersionFieldName()
    {
        return $this->getOriginalFieldName($this->mappedVersionFieldName);
    }

    public function extractId($object, Hydrator $hydrator)
    {
        return $hydrator->extractProperty($object, $this->mappedIdFieldName);
    }

    public function extractIdComposite($object, Hydrator $hydrator)
    {
        return array_map(
            function ($mappedIdFieldName) use ($object, $hydrator) {

                return $hydrator->extractProperty($object, $this->mappedVersionFieldName);
            },
            $this->mappedIdCompositePartFieldName
        );
    }

    public function extractVersion($object, Hydrator $hydrator)
    {
        return $hydrator->extractProperty($object, $this->mappedVersionFieldNamer);
    }

    public function extractField($object, $fieldName, Hydrator $hydrator)
    {
        return $hydrator->extractProperty($object, $fieldName);

        /*
        if (! $this->propertyAccessStrategyEnabled) {

            $idGetter = $this->getterise($fieldName);

            if (method_exists($object, $idGetter)) {
                return $object->$idGetter();
            }

            throw new ObjectMappingException(
                sprintf("L'entité '%s' n'a pas la méthode '%s'.",
                get_class($object)),
                $idGetter
            );
        }

        if (property_exists($object, $idGetter)) {
            return $object->$fieldName;
        }

        throw new ObjectMappingException(
                sprintf("L'entité '%s' n'a pas la propriété '%s'.",
                get_class($object)),
                $fieldName
            );
        */
    }

    public function getIdGetter()
    {
        return $this->idGetter;
    }

    public function getIdSetter()
    {
        return $this->idSetter;
    }

    public function getVersionGetter()
    {
        return $this->versionGetter;
    }

    public function getVersionSetter()
    {
        return $this->versionSetter;
    }

    private function getterise($mappedFieldName)
    {
        return isset($mappedFieldName) ? 'get'.ucfirst($mappedFieldName) : null;
    }

    private static function setterise($mappedFieldName)
    {
        return isset($mappedFieldName) ? 'set'.ucfirst($mappedFieldName) : null;
    }

    /*public function beforeEventsExist()
    {
        return isset($this->onBeforeExtract) || isset($this->onBeforeHydrate);
    }

    public function afterEventsExist()
    {
        return isset($this->onAfterExtract) || isset($this->onAfterHydrate);
    }*/

    private function getDataForField($mappedFieldName, $columnDataName)
    {//echo 'columnDataName => ['.$this->columnDataName.']';

        if (! isset($this->fieldsDataByKey[$mappedFieldName][$columnDataName])) {
            return null;
        }

        return $this->fieldsDataByKey[$mappedFieldName][$columnDataName];
    }

    /**
     * Gets the Source (class and method) which hydrate a field.
     *
     * @return array
     */
    /*
    public function getCustomHydrationSources()
    {
        return $this->customHydrationSource;
    }
    */

    public function hasCustomHydrationSource($mappedFieldName)
    {
        return isset($this->customHydrationSource[$mappedFieldName]);
    }

    public function getFieldsWithCustomHydrationSource()
    {
        return array_keys($this->customHydrationSource);
    }

    public function getCustomHydrationSourceInfo($mappedFieldName)
    {
        return [
            $this->customHydrationSource[$mappedFieldName]['class'],
            $this->customHydrationSource[$mappedFieldName]['method'],
            $this->customHydrationSource[$mappedFieldName]['lazyLoading']
        ];
    }

    /**
     * Sets the Source (class and method) which hydrate a field.
     *
     * @param array $customHydrationSource the hydration sources
     *
     * @return self
     */
    public function setCustomHydrationSources(array $customHydrationSource)
    {
        $this->customHydrationSource = $customHydrationSource;

        return $this;
    }

    /*
    public function getHydrationSourcesByIndexedBySources(array $hydrationSources)
    {
        $key = $annotation->class.$annotation->hydrationMethod;
        if (! isset($sourcesAnnotation[$key])) {
            $sourcesAnnotation[$key] = [];
        }
        $sourcesAnnotation[$key][$mappedFieldName] = (array)$annotation;
    }
    */
}