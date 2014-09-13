<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Kassko\DataAccess\Annotation as OM;
use Doctrine\Common\Annotations\Reader as ReaderInterface;
use Kassko\DataAccess\ClassMetadata\ClassMetadata;

/**
 * Class metadata loader for annotations.
 *
 * @author kko
 */
class AnnotationLoader implements LoaderInterface
{
	private $reader;
    private $objectMetadata;
    private $objectReflectionClass;
    private $valueObjectMetadata;
    private $valueObjectReflectionClass;

    private static $objectAnnotationName = OM\Entity::class;
    private static $columnAnnotationName = OM\Column::class;
	private static $idAnnotationName = OM\Id::class;
    private static $idCompositePartAnnotationName = OM\IdCompositePart::class;
    private static $versionAnnotationName = OM\Version::class;
    private static $transientAnnotationName = OM\Transient::class;
    private static $valueObjectAnnotationName = OM\ValueObject::class;
    private static $objectListenersAnnotationName = OM\EntityListeners::class;
    private static $toOneAnnotationName = OM\ToOne::class;
    private static $toManyAnnotationName = OM\ToMany::class;
    private static $customHydrationSourceAnnotationName = OM\CustomSource::class;

    private static $preExtractAnnotationName = OM\PreExtract::class;
    private static $postExtractAnnotationName = OM\PostExtract::class;
    private static $preHydrateAnnotationName = OM\PreHydrate::class;
    private static $postHydrateAnnotationName = OM\PostHydrate::class;

	public function __construct(ReaderInterface $reader)
	{
		$this->reader = $reader;
	}

    public function loadObjectMetadata(ClassMetadata $objectMetadata, $ressource, $type = null)
    {
        $this->objectMetadata = $objectMetadata;
        $this->objectReflectionClass = $this->objectMetadata->getReflectionClass();

    	$this->loadAnnotationsFromObject();

    	return $this->objectMetadata;
    }

    public function supports($ressource, $type = null)
    {
        return 'annotations' === $type;
    }

    private function loadAnnotationsFromObject()
    {
        $this->loadClassAnnotationsFromObject();
        $this->loadMethodAnnotationsFromObject();
        $this->loadFieldAnnotationsFromObject();
        //$this->objectMetadata->setColumnAnnotationName(self::$columnAnnotationName);
    }

    private function loadAnnotationsFromValueObject()
    {
        foreach ($this->valueObjectReflectionClass->getProperties() as $reflectionProperty) {

            $mappedFieldName = $reflectionProperty->getName();
            $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);
            foreach ($annotations as $annotation) {
                yield $mappedFieldName => $annotation;
            }
        }
    }

    private function loadClassAnnotationsFromObject()
    {
        $annotations = $this->reader->getClassAnnotations($this->objectReflectionClass);
        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case self::$objectAnnotationName:
                    $this->objectMetadata->setRepositoryClass($annotation->repositoryClass);
                    $this->objectMetadata->setObjectReadDateFormat($annotation->readDateFormat);
                    $this->objectMetadata->setObjectWriteDateFormat($annotation->writeDateFormat);
                    $this->objectMetadata->setPropertyAccessStrategyEnabled($annotation->propertyAccessStrategyEnabled);
                    $this->objectMetadata->setMetadataExtensionClass($annotation->metadataExtensionClass);
                    break;

                case self::$objectListenersAnnotationName:
                    $this->objectMetadata->setObjectListenerClasses($annotation->classList);
                    break;
            }
        }
    }

    private function loadMethodAnnotationsFromObject()
    {
        foreach ($this->objectReflectionClass->getMethods() as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();

            $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
            foreach ($annotations as $annotation) {

                switch (get_class($annotation)) {
                    case self::$preExtractAnnotationName:
                        $this->objectMetadata->setOnBeforeExtract($methodName);
                        break;

                    case self::$postExtractAnnotationName:
                        $this->objectMetadata->setOnAfterExtract($methodName);
                        break;

                    case self::$preHydrateAnnotationName:
                        $this->objectMetadata->setOnBeforeHydrate($methodName);
                        break;

                    case self::$postHydrateAnnotationName:
                        $this->objectMetadata->setOnAfterHydrate($methodName);
                        break;
                }
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
        $customHydrationSources = [];
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $valueObjectsByKey = [];
        $valueObjectsClassNames = [];
        $mappedTransientFieldNames = [];
        $mappedManagedFieldNames = [];
        $fieldsWithHydrationStrategy = [];

        foreach ($this->objectReflectionClass->getProperties() as $reflectionProperty) {
            $mappedFieldNames[] = $mappedFieldName = $reflectionProperty->getName();

            $annotationsByKey = [];
            $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);

            $existsColumnAnnotation = false;
            $curseurValueObject = -1;

            foreach ($annotations as $annotation) {
                $annotationName = get_class($annotation);

                switch ($annotationName) {
                    case self::$columnAnnotationName:

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
                        }

                        if (isset($annotation->writeStrategy)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = $annotation->writeStrategy;
                        }

                        if (isset($annotation->readStrategy)) {
                            $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = $annotation->readStrategy;
                        }

                        $annotationsByKey['column'] = (array)$annotation;
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

                    case self::$customHydrationSourceAnnotationName:

                        $customHydrationSources[$mappedFieldName] = (array)$annotation;
                        break;

                    case self::$valueObjectAnnotationName:

                        if (! isset($valueObjectsByKey[$mappedFieldName])) {
                            $valueObjectsByKey[$mappedFieldName] = [];
                        }

                        //On gère le cas où l'annotation ValueObject occupe plusieurs lignes.
                        if (isset($annotation->name)) {

                            $valueObjectsByKey[$mappedFieldName][++$curseurValueObject] = (array)$annotation;
                            $valueObjectsClassNames[] = $annotation->valueObjectClass;
                        } else {

                            $valueObjectsByKey[$mappedFieldName][$curseurValueObject]['fieldNames'] += (array)$annotation->fieldNames;
                        }
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
                }
            }

            $fieldAnnotationsByKey[$mappedFieldName] = $annotationsByKey;
        }

        if (count($fieldAnnotationsByKey)) {
            $this->objectMetadata->setFieldsDataByKey($fieldAnnotationsByKey);
        }

        if (count($mappedFieldNames)) {
            $this->objectMetadata->setMappedFieldNames($mappedFieldNames);
        }

        if (count($originalFieldNames)) {
            $this->objectMetadata->setOriginalFieldNames($originalFieldNames);
        }

        if (count($toOriginal)) {
            $this->objectMetadata->setToOriginal($toOriginal);
        }

        if (count($toMapped)) {
            $this->objectMetadata->setToMapped($toMapped);
        }

        if (count($toOneAssociations)) {
            $this->objectMetadata->setToOneAssociations($toOneAssociations);
        }

        if (count($toManyAssociations)) {
            $this->objectMetadata->setToManyAssociations($toManyAssociations);
        }

        if (count($customHydrationSources)) {
            $this->objectMetadata->setCustomHydrationSources($customHydrationSources);
        }

        if (isset($mappedIdFieldName)) {
            $this->objectMetadata->setMappedIdFieldName($mappedIdFieldName);
        }

        if (count($mappedIdCompositePartFieldName)) {
            $this->objectMetadata->setMappedIdCompositePartFieldName($mappedIdCompositePartFieldName);
        }

        if (isset($mappedVersionFieldName)) {
            $this->objectMetadata->setMappedVersionFieldName($mappedVersionFieldName);
        }

        if (count($mappedDateFieldNames)) {
            $this->objectMetadata->setMappedDateFieldNames($mappedDateFieldNames);
        }

        if (count($valueObjectsByKey)) {
            $this->objectMetadata->setValueObjectsByKey($valueObjectsByKey);
        }

        if (count($valueObjectsClassNames)) {
            $this->objectMetadata->setValueObjectsClassNames(array_unique($valueObjectsClassNames));
        }

        if (count($mappedTransientFieldNames)) {
            $this->objectMetadata->setMappedTransientFieldNames($mappedTransientFieldNames);
        }

        if (count($mappedManagedFieldNames)) {
            $this->objectMetadata->setMappedManagedFieldNames($mappedManagedFieldNames);
        }

        if (count($fieldsWithHydrationStrategy)) {
            $this->objectMetadata->setFieldsWithHydrationStrategy($fieldsWithHydrationStrategy);
        }
    }
}