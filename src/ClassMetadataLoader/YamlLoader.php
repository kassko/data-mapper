<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Symfony\Component\Yaml\Parser;
use Kassko\DataAccess\ClassMetadata\ClassMetadata;

/**
 * Class metadata loader for yaml.
 *
 * @author kko
 */
class YamlLoader implements LoaderInterface
{
    public function loadObjectMetadata(ClassMetadata $objectMetadata, $ressource, $type = null)
    {
        $data = $this->getData($ressource);

        $this->objectMetadata = $objectMetadata;

        $this->loadClassAnnotations($data);
        $this->loadMethodAnnotations($data);
        $this->loadFieldAnnotations($data);

    	return $this->objectMetadata;
    }

    private function loadClassAnnotations(array $data)
    {
        if (isset($data['entity']['repositoryClass'])) {
            $this->objectMetadata->setRepositoryClass($data['entity']['repositoryClass']);
        }

        if (isset($data['entity']['readDateFormat'])) {
            $this->objectMetadata->setObjectReadDateFormat($data['entity']['readDateFormat']);
        }

        if (isset($data['entity']['writeDateFormat'])) {
            $this->objectMetadata->setObjectWriteDateFormat($data['entity']['writeDateFormat']);
        }

        if (isset($data['entity']['propertyAccessStrategyEnabled'])) {
            $this->objectMetadata->setPropertyAccessStrategyEnabled($data['entity']['propertyAccessStrategyEnabled']);
        }

        if (isset($data['entity']['metadataExtensionClass'])) {
            $this->objectMetadata->setMetadataExtensionClass($data['entity']['metadataExtensionClass']);
        }

        if (isset($data['entityListeners'])) {
            $this->objectMetadata->setObjectListenerClasses($data['entityListeners']);
        }
    }

    private function loadMethodAnnotations(array $data)
    {
        if (isset($data['callbacks']['postExtract'])) {
            $this->objectMetadata->setOnBeforeExtract($data['callbacks']['postExtract']);
        }

        if (isset($data['callbacks']['postHydrate'])) {
            $this->objectMetadata->setOnBeforeExtract($data['callbacks']['postHydrate']);
        }

        if (isset($data['callbacks']['preExtract'])) {
            $this->objectMetadata->setOnBeforeExtract($data['callbacks']['preExtract']);
        }

        if (isset($data['callbacks']['preHydrate'])) {
            $this->objectMetadata->setOnBeforeExtract($data['callbacks']['preHydrate']);
        }
    }

    private function loadFieldAnnotations(array $data)
    {
        if (! isset($data['fields'])) {
            return;
        }

        $fieldsDataByKey = [];
        $mappedFieldNames = [];
        $mappedDateFieldNames = [];
        $originalFieldNames = [];
        $toOriginal = [];
        $toMapped = [];
        $toOneAssociations = [];
        $toManyAssociations = [];
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $valueObjectsByKey = [];
        $valueObjectsClassNames = [];
        $mappedTransientFieldNames = [];
        $mappedManagedFieldNames = [];
        $fieldsWithHydrationStrategy = [];

        if (isset($data['id'])) {
            $mappedIdFieldName = $data['id'];
        }

        if (isset($data['idComposite'])) {
            $mappedIdCompositePartFieldName = $data['idComposite'];
        }

        if (isset($data['version'])) {
            $mappedVersionFieldName = $data['version'];
        }

        if (isset($data['transient'])) {
            $mappedTransientFieldNames = $data['transient'];
        }

        $dataName = 'fields';
        foreach ($data[$dataName] as $mappedFieldName => $fieldData) {

            $mappedManagedFieldNames[] = $mappedFieldName;

            if (! isset($fieldData['name'])) {

                $mappedFieldNames[] = $mappedFieldName;
                $originalFieldNames[] = $mappedFieldName;

                $toOriginal[$mappedFieldName] = $mappedFieldName;
                $toMapped[$mappedFieldName] = $mappedFieldName;
            } else {

                $mappedFieldNames[] = $mappedFieldName;
                $originalFieldNames[] = $fieldData['name'];

                $toOriginal[$mappedFieldName] = $fieldData['name'];
                $toMapped[$fieldData['name']] = $mappedFieldName;
            }

            $fieldDataByKey['column'] = $fieldData;

            if (isset($fieldData['type']) && 'date' === $fieldData['type']) {
               $mappedDateFieldNames[] = $mappedFieldName;
            }

            if (isset($fieldData['writeStrategy']) || isset($fieldData['readStrategy'])) {

                $fieldsWithHydrationStrategy[$mappedFieldName] = [];
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = null;
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = null;
            }

            if (isset($fieldData['writeStrategy'])) {
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = $fieldData['writeStrategy'];
            }

            if (isset($fieldData['readStrategy'])) {
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = $fieldData['readStrategy'];
            }

            $fieldsDataByKey[$mappedFieldName] = $fieldDataByKey;
        }

        if (isset($data['toOne'])) {

            foreach ($data['toOne'] as $associationName => $toOne) {
                $toOneAssociations[$mappedFieldName] = ['name' => $associationName] + $data['toOne'];
            }
        }

        if (isset($data['toMany'])) {

            foreach ($data['toMany'] as $associationName => $toMany) {
                $toManyAssociations[$mappedFieldName] = ['name' => $associationName] + $data['toMany'];
            }
        }

        if (isset($data['valueObjects'])) {
            $valueObjectsByKey[$mappedFieldName] = $data['valueObjects'];
        }

        if (count($fieldsDataByKey)) {
            $this->objectMetadata->setFieldsDataByKey($fieldsDataByKey);
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
            $this->objectMetadata->setValueObjectsClassNames($valueObjectsClassNames);
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

    public function supports($ressource, $type = null)
    {
        return 'yml' === $type;
    }

    private function getData($ressource)
    {
        $parser = new Parser();
        $content = file_get_contents($ressource);

        return $parser->parse($content);
    }
}