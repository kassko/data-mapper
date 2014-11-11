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
    private $objectMetadata;

    public function loadClassMetadata(ClassMetadata $classMetadata, $resource, $type = null)
    {
        $data = $this->getData($resource);
        //echo "<pre>"; print_r($data); echo "</pre>";

        $this->classMetadata = $classMetadata;

        $this->loadClassAnnotations($data);
        $this->loadMethodAnnotations($data);
        $this->loadFieldAnnotations($data);

    	return $this->classMetadata;
    }

    private function loadClassAnnotations(array $data)
    {
        if (isset($data['entity']['repositoryClass'])) {
            $this->classMetadata->setRepositoryClass($data['entity']['repositoryClass']);
        }

        if (isset($data['entity']['readDateFormat'])) {
            $this->classMetadata->setObjectReadDateFormat($data['entity']['readDateFormat']);
        }

        if (isset($data['entity']['writeDateFormat'])) {
            $this->classMetadata->setObjectWriteDateFormat($data['entity']['writeDateFormat']);
        }

        if (isset($data['entity']['propertyAccessStrategyEnabled'])) {
            $this->classMetadata->setPropertyAccessStrategyEnabled($data['entity']['propertyAccessStrategyEnabled']);
        }

        if (isset($data['entity']['metadataExtensionClass'])) {
            $this->classMetadata->setMetadataExtensionClass($data['entity']['metadataExtensionClass']);
        }

        if (isset($data['entityListeners'])) {
            $this->classMetadata->setObjectListenerClasses($data['entityListeners']);
        }
    }

    private function loadMethodAnnotations(array $data)
    {
        if (isset($data['callbacks']['postExtract'])) {
            $this->classMetadata->setOnBeforeExtract($data['callbacks']['postExtract']);
        }

        if (isset($data['callbacks']['postHydrate'])) {
            $this->classMetadata->setOnBeforeExtract($data['callbacks']['postHydrate']);
        }

        if (isset($data['callbacks']['preExtract'])) {
            $this->classMetadata->setOnBeforeExtract($data['callbacks']['preExtract']);
        }

        if (isset($data['callbacks']['preHydrate'])) {
            $this->classMetadata->setOnBeforeExtract($data['callbacks']['preHydrate']);
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
        $providers = [];
        $valueObjects = [];
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
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

            if (! isset($fieldData['type'])) {
                $fieldData['type'] = 'string';
            }

            $fieldDataByKey['column'] = $fieldData;

            if ('date' === $fieldData['type']) {
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

            foreach ($data['toOne'] as $associationName => $toOneData) {
                $toOneAssociations[$mappedFieldName][] = ['name' => $associationName] + $toOneData;
            }
        }

        if (isset($data['toMany'])) {

            foreach ($data['toMany'] as $associationName => $toManyData) {
                $toManyAssociations[$mappedFieldName][] = ['name' => $associationName] + $toManyData;
            }
        }

        if (isset($data['provider'])) {
            $providers[$mappedFieldName] = $data['provider'];
        }

        if (isset($data['valueObjects'])) {

            foreach ($data['valueObjects'] as $valueObjectName => $valueObjectData) {
                $valueObjects[$mappedFieldName] = $valueObjectData;
            }
        }

        if (count($fieldsDataByKey)) {
            $this->classMetadata->setFieldsDataByKey($fieldsDataByKey);
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

        if (count($valueObjects)) {
            $this->classMetadata->setValueObjects($valueObjects);
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

        if (count($mappedTransientFieldNames)) {
            $this->classMetadata->setMappedTransientFieldNames($mappedTransientFieldNames);
        }

        if (count($mappedManagedFieldNames)) {
            $this->classMetadata->setMappedManagedFieldNames($mappedManagedFieldNames);
        }

        if (count($fieldsWithHydrationStrategy)) {
            $this->classMetadata->setFieldsWithHydrationStrategy($fieldsWithHydrationStrategy);
        }
    }

    public function supports($resource, $type = null)
    {
        return 'yaml' === $type;
    }

    private function getData($resource)
    {
        $parser = new Parser();
        return $this->doGetData($resource, $parser);
    }

    private function doGetData($resource, Parser $parser)
    {
        $content = file_get_contents($resource);
        $data = $parser->parse($content);

        if (isset($data['imports'])) {
            $resourceDir = dirname($resource);

            foreach ($data['imports'] as $import) {

                if (isset($import['resource'])) {

                    $otherResource = $import['resource'];
                    if ('.' === $otherResourceDir = dirname($otherResource)) {
                        $otherResource = $resourceDir.'/'.$otherResource;
                    }

                    $othersData = $this->doGetData($otherResource, $parser);
                    $data = array_merge_recursive($othersData, $data);
                }
            }
        }

        return $data;
    }
}