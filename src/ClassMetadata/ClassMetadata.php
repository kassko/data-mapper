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
    private $valueObjects = [];
    private $repositoryClass;
    private $customHydrator;
    private $objectReadDateFormat;
    private $objectWriteDateFormat;
    private $propertyAccessStrategyEnabled;

    /**
     * @var string Fqcn de la classes contenant les métadonnées de type "callback"
     */
    private $metadataExtensionClass;

    private $mappedManagedFieldNames = [];
    private $mappedTransientFieldNames = [];
    private $fieldsWithHydrationStrategy = [];
    private $toOneAssociations = [];
    private $toManyAssociations = [];
    private $getters = [];
    private $setters = [];

    /**
     * Source (class and method) which hydrate a field.
     * @var array
     */
    private $providers = [];
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

            unset($fieldDataByKey);
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

    public function isSingleValuedAssociation($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->toOneAssociations);
    }

    public function isCollectionValuedAssociation($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->toManyAssociations);
    }

    public function getSingleValuedAssociations()
    {
        return array_keys($this->toOneAssociations);
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

    /*
    public function isValueObject($mappedFieldName)
    {
        return array_key_exists($mappedFieldName, $this->valueObjects);
    }*/

    public function getFieldsWithValueObjects()
    {
        return array_keys($this->valueObjects);
    }

    public function setValueObjects(array $valueObjects)
    {
        $this->valueObjects = $valueObjects;
        return $this;
    }

    public function getValueObjectInfo($mappedFieldName)
    {
        $valueObjectInfo = $this->valueObjects[$mappedFieldName];

        $mappingResourcePath = null;
        if (isset($valueObjectInfo['mappingResourcePath'], $valueObjectInfo['mappingResourceName'])) {
            $mappingResourcePath = $valueObjectInfo['mappingResourcePath'].'/'.$valueObjectInfo['mappingResourceName'];
        } elseif (isset($valueObjectInfo['mappingResourceName'])) {
            $mappingResourcePath = $valueObjectInfo['mappingResourceName'];
        }

        return [
            $valueObjectInfo['class'],
            $mappingResourcePath,
            isset($valueObjectInfo['mappingResourceType']) ? $valueObjectInfo['mappingResourceType'] : null,
        ];
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

    public function getFieldsWithHydrationStrategy()
    {
        return $this->fieldsWithHydrationStrategy;
    }

    public function setFieldsWithHydrationStrategy(array $fieldsWithHydrationStrategy)
    {
        $this->fieldsWithHydrationStrategy = $fieldsWithHydrationStrategy;

        return $this;
    }

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

    public function getOriginalFieldNames()
    {
        return $this->originalFieldNames;
    }

    public function getTypeOfMappedField($mappedFieldName)
    {
        if (null != $data = $this->getDataForField($mappedFieldName, $this->columnDataName)) {
            return $data['type'];
        }

        return 'string';
    }

    public function getOriginalFieldName($mappedFieldName)
    {
        if (! isset($this->toOriginal[$mappedFieldName])) {
            return $mappedFieldName;
        }

        return $this->toOriginal[$mappedFieldName];
    }

    public function getMappedFieldName($originalFieldName)
    {
        if (! isset($this->toMapped[$originalFieldName])) {
            return $originalFieldName;
        }

        return $this->toMapped[$originalFieldName];
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
    {
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
        if (! isset($this->mappedIdFieldName)) {
            throw new ObjectMappingException(sprintf('In your use case, the Id field name is needed for object "%s"', $this->getName()));
        }
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
    }

    public function getIdGetter()
    {
        if (null === $this->idGetter) {
            $this->idGetter = $this->getterise($this->mappedIdFieldName);
        }

        return $this->idGetter;
    }

    public function getIdSetter()
    {
        if (null === $this->idSetter) {
            $this->idSetter = $this->setterise($this->mappedIdFieldName);
        }

        return $this->idSetter;
    }

    public function getVersionGetter()
    {
        if (null === $this->versionGetter) {
            $this->versionGetter = $this->getterise($this->mappedVersionFieldName);
        }

        return $this->versionGetter;
    }

    public function getVersionSetter()
    {
        if (null === $this->versionSetter) {
            $this->versionSetter = $this->setterise($this->mappedVersionFieldName);
        }

        return $this->versionSetter;
    }

    public function getterise($mappedFieldName)
    {
        if (! isset($mappedFieldName)) {
            return null;
        }

        if (isset($this->getters[$mappedFieldName])) {

            if (isset($this->getters[$mappedFieldName]['name'])) {
                return $this->getters[$mappedFieldName]['name'];
            }

            switch ($type = $this->getters[$mappedFieldName]['type']) {

                case 'get':
                case 'is':
                case 'has':
                    return $type.ucfirst($mappedFieldName);
            }

            throw ObjectMappingException::invalidGetter($mappedFieldName, $this->getters[$mappedFieldName]);
        }

        return 'get'.ucfirst($mappedFieldName);
    }

    public function setterise($mappedFieldName)
    {
        if (! isset($mappedFieldName)) {
            return null;
        }

        if (isset($this->setters[$mappedFieldName])) {

            if (isset($this->setters[$mappedFieldName]['name'])) {
                return $this->setters[$mappedFieldName]['name'];
            }

            switch ($type = $this->setters[$mappedFieldName]['type']) {

                case 'set':
                    return $type.ucfirst($mappedFieldName);
            }

            throw ObjectMappingException::invalidSetter($mappedFieldName, $this->setters[$mappedFieldName]);
        }

        return 'set'.ucfirst($mappedFieldName);
    }

    private function getDataForField($mappedFieldName, $columnDataName)
    {
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
    public function getProviders()
    {
        return $this->providers;
    }
    */

    public function hasProvider($mappedFieldName)
    {
        return isset($this->providers[$mappedFieldName]);
    }

    public function getFieldsWithProviders()
    {
        return array_keys($this->providers);
    }

    public function getProvidersInfo($mappedFieldName)
    {
        return [
            $this->providers[$mappedFieldName]['class'],
            $this->providers[$mappedFieldName]['method'],
            $this->providers[$mappedFieldName]['lazyLoading']
        ];
    }

    /**
     * Sets the Source (class and method) which hydrate a field.
     *
     * @param array $providers the hydration sources
     *
     * @return self
     */
    public function setProviders(array $providers)
    {
        $this->providers = $providers;

        return $this;
    }

    public function setGetters(array $getters)
    {
        $this->getters = $getters;

        return $this;
    }

    public function setSetters(array $setters)
    {
        $this->setters = $setters;

        return $this;
    }

    /**
     * Retrieve fields with the same provider as $mappedFieldNameRef.
     *
     * @param array $mappedFieldNameRef The reference field.
     *
     * @return array
     */
    public function getFieldsWithSameProvider($mappedFieldNameRef)
    {
        if (! isset($this->providers[$mappedFieldNameRef])) {
            throw new ObjectMappingException(sprintf('A "provider" metadata is expected for the field "%s".', $mappedFieldNameRef));
        }

        $class = $this->providers[$mappedFieldNameRef]['class'];
        $method = $this->providers[$mappedFieldNameRef]['method'];

        $propLoadedTogether = [];
        foreach ($this->providers as $mappedFieldName => $value) {

            if ($mappedFieldName !== $mappedFieldNameRef && $value['class'] === $class && $value['method'] === $method) {
                $propLoadedTogether[] = $mappedFieldName;
            }
        }

        return $propLoadedTogether;
    }

    public function existsMappedFieldName($mappedFieldName)
    {
        return in_array($mappedFieldName, $this->mappedFieldNames);
    }

    public function hasCustomHydrator()
    {
        return isset($this->customHydrator);
    }

    public function setCustomHydrator(array $customHydrator)
    {
        $this->customHydrator = $customHydrator;
    }

    public function getCustomHydratorInfo()
    {
        return [
            $this->customHydrator['class'],
            $this->customHydrator['hydrateMethod'],
            $this->customHydrator['extractMethod'],
        ];
    }
}
