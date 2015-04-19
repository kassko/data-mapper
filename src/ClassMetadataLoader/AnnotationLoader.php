<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Doctrine\Common\Annotations\Reader as ReaderInterface;
use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

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

    private static $objectAnnotationName = DM\Object::class;
    private static $fieldAnnotationName = DM\Field::class;
    private static $includeAnnotationName = 'Kassko\\DataMapper\\Annotation\\Include';//DM\Include::class;
    private static $excludeAnnotationName = DM\Exclude::class;
    private static $idAnnotationName = DM\Id::class;
    private static $idCompositePartAnnotationName = DM\IdCompositePart::class;
    private static $versionAnnotationName = DM\Version::class;
    private static $transientAnnotationName = DM\Transient::class;
    private static $valueObjectAnnotationName = DM\ValueObject::class;
    private static $customHydratorAnnotationName = DM\CustomHydrator::class;
    private static $objectListenersAnnotationName = DM\ObjectListeners::class;
    
    private static $dataSourceAnnotationName = DM\DataSource::class;
    private static $providerAnnotationName = DM\Provider::class;
    private static $refSourceAnnotationName = DM\RefSource::class;
    private static $dataSourcesStoreAnnotationName = DM\DataSourcesStore::class;
    private static $providersStoreAnnotationName = DM\ProvidersStore::class;
    private static $noSourceAnnotationName = DM\NoSource::class;
    private static $defaultSourceAnnotationName = DM\RefDefaultSource::class;
    
    private static $getterAnnotationName = DM\Getter::class;
    private static $setterAnnotationName = DM\Setter::class;
    private static $preExtractAnnotationName = DM\PreExtract::class;
    private static $postExtractAnnotationName = DM\PostExtract::class;
    private static $preHydrateAnnotationName = DM\PreHydrate::class;
    private static $postHydrateAnnotationName = DM\PostHydrate::class;

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
                    $this->classMetadata->setFieldExclusionPolicy($annotation->fieldExclusionPolicy);
                    $this->classMetadata->setRepositoryClass($annotation->providerClass);
                    $this->classMetadata->setObjectReadDateFormat($annotation->readDateConverter);
                    $this->classMetadata->setObjectWriteDateFormat($annotation->writeDateConverter);
                    $this->classMetadata->setPropertyAccessStrategyEnabled($annotation->propertyAccessStrategy);
                    $this->classMetadata->setPropertyMetadataExtensionClass($annotation->fieldMappingExtensionClass);
                    $this->classMetadata->setClassMetadataExtensionClass($annotation->classMappingExtensionClass);
                    break;

                case self::$objectListenersAnnotationName:
                    $this->classMetadata->setObjectListenerClasses($annotation->classList);
                    break;

                case self::$dataSourcesStoreAnnotationName:
                    foreach ($annotation->items as &$item) {
                        $item = (array)$item;
                    }
                    $this->classMetadata->setDataSourcesStore($annotation->items);
                    break;

                case self::$providersStoreAnnotationName:
                    $this->classMetadata->setProvidersStore($annotation->items);
                    break;

                case self::$defaultSourceAnnotationName:
                    $this->classMetadata->setRefDefaultSource($annotation->id);
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
        $includedFields = [];
        $excludedFields = [];
        $toOriginal = [];
        $toMapped = [];
        $dataSources = [];
        $providers = [];
        $refSources = []; 
        $fieldsWithSourcesForbidden = []; 
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $valueObjects = [];
        $mappedTransientFieldNames = [];
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

                        if (isset($annotation->writeConverter) || isset($annotation->readConverter)) {

                            $fieldsWithHydrationStrategy[$mappedFieldName] = [];
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = null;
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = null;
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTENSION_CLASS] = null;
                        }

                        if (isset($annotation->writeConverter)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = $annotation->writeConverter;
                        }

                        if (isset($annotation->readConverter)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = $annotation->readConverter;
                        }

                        if (isset($annotation->mappingExtensionClass)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTENSION_CLASS] = $annotation->mappingExtensionClass;
                        }

                        $annotationsByKey['field'] = (array)$annotation;
                        break;

                    case self::$includeAnnotationName:
                        $includedFields[$mappedFieldName] = true;
                        break;

                    case self::$excludeAnnotationName:
                        $excludedFields[$mappedFieldName] = true;
                        break;

                    case self::$dataSourceAnnotationName:
                        $annotation->preprocessor = (array)$annotation->preprocessor;
                        $annotation->processor = (array)$annotation->processor;      
                        
                        $annotation->preprocessors = (array)$annotation->preprocessors;
                        foreach ($annotation->preprocessors['items'] as &$preprocessor) {
                            $preprocessor = (array)$preprocessor;
                        }
                        
                        foreach ($annotation->processors as &$processor) {
                            $processor = (array)$processor;
                        }

                        $dataSources[$mappedFieldName] = (array)$annotation;
                        break;

                    case self::$providerAnnotationName:
                        $annotation->preprocessor = (array)$annotation->preprocessor;
                        $annotation->processor = (array)$annotation->processor;
                        
                        $annotation->preprocessors = (array)$annotation->preprocessors;
                        foreach ($annotation->preprocessors['items'] as &$preprocessor) {
                            $preprocessor = (array)$preprocessor;
                        }
                        
                        foreach ($annotation->processors as &$processor) {
                            $processor = (array)$processor;
                        }

                        $providers[$mappedFieldName] = (array)$annotation;
                        break;

                    case self::$refSourceAnnotationName:

                        //ref is deprecated, it should be removed in the next significant release. 
                        $data = (array)$annotation;
                        $refSources[$mappedFieldName] = isset($annotation->id) ? $annotation->id : $annotation->ref;
                        break; 

                    case self::$noSourceAnnotationName:

                        $fieldsWithSourcesForbidden[$mappedFieldName] = true;
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

        if (count($includedFields)) {
            $this->classMetadata->setIncludedFields($includedFields);
        }

        if (count($excludedFields)) {
            $this->classMetadata->setExcludedFields($excludedFields);
        }

        if (count($toOriginal)) {
            $this->classMetadata->setToOriginal($toOriginal);
        }

        if (count($toMapped)) {
            $this->classMetadata->setToMapped($toMapped);
        }

        if (count($dataSources)) {
            $this->classMetadata->setDataSources($dataSources);
        }

        if (count($providers)) {
            $this->classMetadata->setProviders($providers);
        }

        if (count($refSources)) {
            $this->classMetadata->setRefSources($refSources);
        }

        if (count($fieldsWithSourcesForbidden)) {
            $this->classMetadata->setFieldsWithSourcesForbidden($fieldsWithSourcesForbidden);
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