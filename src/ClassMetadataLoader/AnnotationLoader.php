<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Doctrine\Common\Annotations\Reader as ReaderInterface;
use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\ClassMetadata\Model;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Class metadata loader for annotations format.
 *
 * @author kko
 */
class AnnotationLoader implements LoaderInterface
{
    private $reader;
    private $classMetadata;
    private $objectReflectionClass;

    private static $objectAnnotationName = DM\Object::class;
    private static $listenersAnnotationName = DM\Listeners::class;
    private static $fieldAnnotationName = DM\Field::class;
    private static $includeAnnotationName = DM\ToInclude::class;
    private static $excludeAnnotationName = DM\ToExclude::class;
    private static $oldExcludeAnnotationName = DM\Exclude::class;//Deprecated. Use DM\ToExclude instead. 
    private static $idAnnotationName = DM\Id::class;
    private static $idCompositePartAnnotationName = DM\IdCompositePart::class;
    private static $versionAnnotationName = DM\Version::class;
    private static $transientAnnotationName = DM\Transient::class;
    private static $configAnnotationName = DM\Config::class;
    private static $valueObjectAnnotationName = DM\ValueObject::class;
    private static $customHydratorAnnotationName = DM\CustomHydrator::class;
    private static $objectListenersAnnotationName = DM\ObjectListeners::class;
    
    private static $dataSourceAnnotationName = DM\DataSource::class;
    private static $providerAnnotationName = DM\Provider::class;
    private static $refSourceAnnotationName = DM\RefSource::class;
    private static $dataSourcesStoreAnnotationName = DM\DataSourcesStore::class;
    private static $providersStoreAnnotationName = DM\ProvidersStore::class;
    private static $excludeImplicitSourceAnnotationName = DM\ExcludeImplicitSource::class;
    private static $implicitSourceAnnotationName = DM\RefImplicitSource::class;
    
    private static $getterAnnotationName = DM\Getter::class;
    private static $setterAnnotationName = DM\Setter::class;
    private static $variablesAnnotationName = DM\Variables::class;
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
        DelegatingLoader $delegatingLoader = null
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

                case self::$listenersAnnotationName:
                    foreach ($annotation->preHydrate->items as $listener) {
                        $this->classMetadata->addPreHydrateListener(new Model\Method($listener->class, $listener->method, $listener->args));
                    }
                    foreach ($annotation->postHydrate->items as $listener) {
                        $this->classMetadata->addPostHydrateListener(new Model\Method($listener->class, $listener->method, $listener->args));
                    }
                    foreach ($annotation->preExtract->items as $listener) {
                        $this->classMetadata->addPreExtractListener(new Model\Method($listener->class, $listener->method, $listener->args));
                    }
                    foreach ($annotation->postExtract->items as $listener) {
                        $this->classMetadata->addPostExtractListener(new Model\Method($listener->class, $listener->method, $listener->args));
                    }
                    break;

                case self::$objectListenersAnnotationName:
                    $this->classMetadata->setObjectListenerClasses($annotation->classList);
                    break;

                case self::$dataSourcesStoreAnnotationName:

                    $dataSources = [];
                    foreach ($annotation->items as $item) {
                        $dataSource = new Model\DataSource();
                        $this->loadSource($dataSource, $item);
                        $dataSources[] = $dataSource;
                    }
                    $this->classMetadata->setDataSourcesStore($dataSources);
                    break;

                case self::$providersStoreAnnotationName:

                    $providers = [];
                    foreach ($annotation->items as $item) {
                        $provider = new Model\Provider();
                        $this->loadSource($provider, $item);
                        $providers[] = $provider;
                    }
                    $this->classMetadata->setProvidersStore($providers);
                    break;

                case self::$implicitSourceAnnotationName:
                    $this->classMetadata->setRefImplicitSource($annotation->id);
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
        $fieldsNotToBindAutoToImplicitSource = []; 
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $valueObjects = [];
        $mappedTransientFieldNames = [];
        $fieldsWithHydrationStrategy = [];
        $getters = [];
        $setters = [];
        $variables = [];

        foreach ($this->objectReflectionClass->getProperties() as $reflectionProperty) {
            $mappedFieldNames[] = $mappedFieldName = $reflectionProperty->getName();

            $annotationsByKey = [];
            $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);

            $existsFieldAnnotation = false;

            $toOriginal[$mappedFieldName] = $mappedFieldName;
            $toMapped[$mappedFieldName] = $mappedFieldName;
            $originalFieldNames[$mappedFieldName] = $mappedFieldName;//Todo: remove it. It's useless now.

            foreach ($annotations as $annotation) {
                $annotationName = get_class($annotation);

                switch ($annotationName) {
                    case self::$fieldAnnotationName:

                        if (! empty($annotation->name)) {
                            $toOriginal[$mappedFieldName] = $annotation->name;
                            $toMapped[$annotation->name] = $mappedFieldName;
                            $originalFieldNames[$mappedFieldName] = $annotation->name;
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

                    case self::$variablesAnnotationName:
                        $variables[$mappedFieldName] = (array)$annotation->variables['value'];
                        break;

                    case self::$includeAnnotationName:
                        $includedFields[$mappedFieldName] = true;
                        break;

                    case self::$excludeAnnotationName:
                    case self::$oldExcludeAnnotationName:
                        $excludedFields[$mappedFieldName] = true;
                        break;

                    case self::$dataSourceAnnotationName:

                        $dataSource = new Model\DataSource();
                        $this->loadSource($dataSource, $annotation);

                        $dataSources[$mappedFieldName] = $dataSource;
                        break;

                    case self::$providerAnnotationName:
                        //Provider is a data source now.
                        //This section should be refactored on the next significant release with "provider" removing.
                        $provider = new Model\Provider();
                        $this->loadSource($provider, $annotation);

                        $providers[$mappedFieldName] = $provider;
                        break;

                    case self::$refSourceAnnotationName:

                        //ref is deprecated, it should be removed in the next significant release. 
                        $data = (array)$annotation;
                        $refSources[$mappedFieldName] = isset($annotation->id) ? $annotation->id : $annotation->ref;
                        break; 

                    case self::$excludeImplicitSourceAnnotationName:

                        $fieldsNotToBindAutoToImplicitSource[$mappedFieldName] = true;
                        break;             

                    case self::$configAnnotationName:
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

        if (count($fieldsNotToBindAutoToImplicitSource)) {
            $this->classMetadata->setFieldsNotToBindAutoToImplicitSource($fieldsNotToBindAutoToImplicitSource);
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

        if (count($variables)) {
            $this->classMetadata->setVariables($variables);
        }
    }

    private function loadSource(Model\Source $sourceModel, DM\Source $annotation)
    {
        $sourceModel
        ->setId($annotation->id)
        ->setMethod(new Model\Method($annotation->class, $annotation->method, $annotation->args))
        ->setLazyLoading($annotation->lazyLoading)
        ->setSupplySeveralFields($annotation->supplySeveralFields)
        ->setOnFail($annotation->onFail)
        ->setExceptionClass($annotation->exceptionClass)
        ->setBadReturnValue($annotation->badReturnValue)
        ->setFallbackSourceId($annotation->fallbackSourceId)
        ->setDepends($annotation->depends)
        ;

        if (isset($annotation->preprocessor->method)) {
            $sourceModel->addPreprocessor(
                new Model\Method(
                    $annotation->preprocessor->class,
                    $annotation->preprocessor->method,
                    $annotation->preprocessor->args
                )
            );
        } elseif (isset($annotation->preprocessors->items)) {
            foreach ($annotation->preprocessors->items as $preprocessor) {
                $sourceModel->addPreprocessor(
                    new Model\Method(
                        $preprocessor->class,
                        $preprocessor->method,
                        $preprocessor->args
                    )
                );
            }
        }

        if (isset($annotation->processor->method)) {
            $sourceModel->addProcessor(
                new Model\Method(
                    $annotation->processor->class,
                    $annotation->processor->method,
                    $annotation->processor->args
                )
            );
        } elseif (isset($annotation->processors->items)) {
            foreach ($annotation->processors->items as $processor) {
                $sourceModel->addProcessor(
                    new Model\Method(
                        $processor->class,
                        $processor->method,
                        $processor->args
                    )
                );
            }
        }
    }
}
