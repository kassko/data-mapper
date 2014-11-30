<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Doctrine\Common\Annotations\Reader as ReaderInterface;
use Kassko\DataAccess\Annotation as OM;
use Kassko\DataAccess\ClassMetadata\ClassMetadata;
use Kassko\DataAccess\Configuration\Configuration;

/**
 * Class metadata loader for annotations format.
 *
 * @author kko
 */
class AnnotationLoader extends AbstractLoader
{
    private $reader;
    private $classMetadata;
    private $objectReflectionClass;

    private static $objectAnnotationName = OM\Object::class;
    private static $fieldAnnotationName = OM\Field::class;
    private static $idAnnotationName = OM\Id::class;
    private static $idCompositePartAnnotationName = OM\IdCompositePart::class;
    private static $versionAnnotationName = OM\Version::class;
    private static $transientAnnotationName = OM\Transient::class;
    private static $valueObjectAnnotationName = OM\ValueObject::class;
    private static $customHydratorAnnotationName = OM\CustomHydrator::class;
    private static $objectListenersAnnotationName = OM\ObjectListeners::class;
    private static $toOneAnnotationName = OM\ToOne::class;
    private static $toManyAnnotationName = OM\ToMany::class;
    private static $providerAnnotationName = OM\Provider::class;
    private static $getterAnnotationName = OM\Getter::class;
    private static $setterAnnotationName = OM\Setter::class;

    private static $preExtractAnnotationName = OM\PreExtract::class;
    private static $postExtractAnnotationName = OM\PostExtract::class;
    private static $preHydrateAnnotationName = OM\PreHydrate::class;
    private static $postHydrateAnnotationName = OM\PostHydrate::class;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    public function loadClassMetadata(
        ClassMetadata $classMetadata,
        LoadingCriteriaInterface $loadingCriteria,
        Configuration $configuration,
        LoaderInterface $loader = null
    ) {
        $this->classMetadata = $classMetadata;
        $this->objectReflectionClass = $this->classMetadata->getReflectionClass();

        $this->loadAnnotationsFromObject();

        return $this->classMetadata;
    }

    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return 'annotations' === $loadingCriteria->getResourceType();
    }

    private function loadAnnotationsFromObject()
    {
        $this->loadClassAnnotationsFromObject();
        $this->loadFieldAnnotationsFromObject();
    }

    private function loadClassAnnotationsFromObject()
    {
        $annotations = $this->reader->getClassAnnotations($this->objectReflectionClass);

        foreach ($annotations as $annotation) {

            switch (get_class($annotation)) {
                case self::$objectAnnotationName:
                    $this->classMetadata->setRepositoryClass($annotation->repositoryClass);
                    $this->classMetadata->setObjectReadDateFormat($annotation->readDateFormat);
                    $this->classMetadata->setObjectWriteDateFormat($annotation->writeDateFormat);
                    $this->classMetadata->setPropertyAccessStrategyEnabled($annotation->propertyAccessStrategy);
                    $this->classMetadata->setPropertyMetadataExtensionClass($annotation->fieldMappingExtensionClass);
                    $this->classMetadata->setClassMetadataExtensionClass($annotation->classMappingExtensionClass);
                    break;

                case self::$objectListenersAnnotationName:
                    $this->classMetadata->setObjectListenerClasses($annotation->classList);
                    break;

                case self::$customHydratorAnnotationName:
                    $this->classMetadata->setCustomHydrator((array)$annotation);
                    break;

                case self::$preExtractAnnotationName:
                        $this->classMetadata->setOnBeforeExtract($annotation->method);
                        break;

                case self::$postExtractAnnotationName:
                    $this->classMetadata->setOnAfterExtract($annotation->method);
                    break;

                case self::$preHydrateAnnotationName:
                    $this->classMetadata->setOnBeforeHydrate($annotation->method);
                    break;

                case self::$postHydrateAnnotationName:
                    $this->classMetadata->setOnAfterHydrate($annotation->method);
                    break;
            }
        }
    }

    private function loadFieldAnnotationsFromObject()
    {
        $fieldAnnotationsByKey = [];
        $mappedFieldNames = [];
        $mappedDateFieldNames = [];
        $originalFieldNames = [];
        $toOriginal = [];
        $toMapped = [];
        $toOneAssociations = [];
        $toManyAssociations = [];
        $providers = [];
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $valueObjects = [];
        $mappedTransientFieldNames = [];
        $mappedManagedFieldNames = [];
        $fieldsWithHydrationStrategy = [];
        $getters = [];
        $setters = [];

        foreach ($this->objectReflectionClass->getProperties() as $reflectionProperty) {
            $mappedFieldNames[] = $mappedFieldName = $reflectionProperty->getName();

            $annotationsByKey = [];
            $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);

            $existsFieldAnnotation = false;

            foreach ($annotations as $annotation) {
                $annotationName = get_class($annotation);

                switch ($annotationName) {
                    case self::$fieldAnnotationName:

                        $mappedManagedFieldNames[$mappedFieldName] = $mappedFieldName;

                        if (! isset($annotation->name)) {

                            $toOriginal[$mappedFieldName] = $mappedFieldName;
                            $toMapped[$mappedFieldName] = $mappedFieldName;
                            $originalFieldNames[] = $mappedFieldName;
                        } else {

                            $toOriginal[$mappedFieldName] = $annotation->name;
                            $toMapped[$annotation->name] = $mappedFieldName;
                            $originalFieldNames[] = $annotation->name;
                        }

                        if ('date' === $annotation->type) {
                           $mappedDateFieldNames[] = $mappedFieldName;
                        }

                        if (isset($annotation->writeStrategy) || isset($annotation->readStrategy)) {

                            $fieldsWithHydrationStrategy[$mappedFieldName] = [];
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = null;
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = null;
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTENSION_CLASS] = null;
                        }

                        if (isset($annotation->writeStrategy)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = $annotation->writeStrategy;
                        }

                        if (isset($annotation->readStrategy)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = $annotation->readStrategy;
                        }

                        if (isset($annotation->mappingExtensionClass)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTENSION_CLASS] = $annotation->mappingExtensionClass;
                        }

                        $annotationsByKey['field'] = (array)$annotation;
                        break;

                    case self::$toOneAnnotationName:
                        $toOneAssociations[$mappedFieldName] = (array)$annotation;
                        break;

                    case self::$toManyAnnotationName:
                        $toManyAssociations[$mappedFieldName] = (array)$annotation;

                        if (! isset($toManyAssociations[$mappedFieldName]['name']) && isset($toManyAssociations[$mappedFieldName]['entityClass'])) {

                            $toManyAssociations[$mappedFieldName]['name'] = substr(strrchr($toManyAssociations[$mappedFieldName]['entityClass'], "\\"), 1);
                        }
                        break;

                    case self::$providerAnnotationName:

                        $providers[$mappedFieldName] = (array)$annotation;
                        break;

                    case self::$valueObjectAnnotationName:

                        $valueObjects[$mappedFieldName] = [];
                        $valueObjects[$mappedFieldName]['class'] = $annotation->class;
                        $valueObjects[$mappedFieldName]['mappingResourceName'] = $annotation->mappingResourceName;
                        $valueObjects[$mappedFieldName]['mappingResourcePath'] = $annotation->mappingResourcePath;
                        $valueObjects[$mappedFieldName]['mappingResourceType'] = $annotation->mappingResourceType;
                        break;

                    case self::$idAnnotationName:
                        $mappedIdFieldName = $mappedFieldName;
                        break;

                    case self::$idCompositePartAnnotationName:
                        $mappedIdCompositePartFieldName[] = $mappedFieldName;
                        break;

                    case self::$versionAnnotationName:
                        $mappedVersionFieldName = $mappedFieldName;
                        break;

                    case self::$transientAnnotationName:
                        $mappedTransientFieldNames[] = $mappedFieldName;
                        break;

                    case self::$getterAnnotationName:
                        $getters[$mappedFieldName] = (array)$annotation;
                        break;

                    case self::$setterAnnotationName:
                        $setters[$mappedFieldName] = (array)$annotation;
                        break;
                }
            }

            $fieldAnnotationsByKey[$mappedFieldName] = $annotationsByKey;
        }

        if (count($fieldAnnotationsByKey)) {
            $this->classMetadata->setFieldsDataByKey($fieldAnnotationsByKey);
        }

        if (count($mappedFieldNames)) {
            $this->classMetadata->setMappedFieldNames($mappedFieldNames);
        }

        if (count($originalFieldNames)) {
            $this->classMetadata->setOriginalFieldNames($originalFieldNames);
        }

        if (count($toOriginal)) {
            $this->classMetadata->setToOriginal($toOriginal);
        }

        if (count($toMapped)) {
            $this->classMetadata->setToMapped($toMapped);
        }

        if (count($toOneAssociations)) {
            $this->classMetadata->setToOneAssociations($toOneAssociations);
        }

        if (count($toManyAssociations)) {
            $this->classMetadata->setToManyAssociations($toManyAssociations);
        }

        if (count($providers)) {
            $this->classMetadata->setProviders($providers);
        }

        if (isset($mappedIdFieldName)) {
            $this->classMetadata->setMappedIdFieldName($mappedIdFieldName);
        }

        if (count($mappedIdCompositePartFieldName)) {
            $this->classMetadata->setMappedIdCompositePartFieldName($mappedIdCompositePartFieldName);
        }

        if (isset($mappedVersionFieldName)) {
            $this->classMetadata->setMappedVersionFieldName($mappedVersionFieldName);
        }

        if (count($mappedDateFieldNames)) {
            $this->classMetadata->setMappedDateFieldNames($mappedDateFieldNames);
        }

        if (count($valueObjects)) {
            $this->classMetadata->setValueObjects($valueObjects);
        }

        if (count($mappedTransientFieldNames)) {
            $this->classMetadata->setMappedTransientFieldNames($mappedTransientFieldNames);
        }

        if (count($mappedManagedFieldNames)) {
            $this->classMetadata->setMappedManagedFieldNames($mappedManagedFieldNames);
        }

        if (count($fieldsWithHydrationStrategy)) {
            $this->classMetadata->setFieldsWithHydrationStrategy($fieldsWithHydrationStrategy);
        }

        if (count($getters)) {
            $this->classMetadata->setGetters($getters);
        }

        if (count($setters)) {
            $this->classMetadata->setSetters($setters);
        }
    }
}