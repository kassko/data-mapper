<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;
use Symfony\Component\Yaml\Parser;

/**
 * Class metadata loader for yaml embeded in objects.
 *
 * @author kko
 */
class InnerYamlLoader extends AbstractLoader
{
    private $classMetadata;

    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return
            'inner_yaml' === $loadingCriteria->getResourceType()
            &&
            method_exists($loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod())
        ;
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
        $callable = [$loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod()];
        return $this->parseContent($callable());
    }

    protected function doLoadClassMetadata(ClassMetadata $classMetadata, array $data)
    {
        $this->classMetadata = $classMetadata;

        $this->loadClassAnnotations($data);
        $this->loadFieldAnnotations($data);

        return $this->classMetadata;
    }

    private function loadClassAnnotations(array $data)
    {
        if (isset($data['object']['fieldExclusionPolicy'])) {
            $this->classMetadata->setFieldExclusionPolicy($data['object']['fieldExclusionPolicy']);
        }

        if (isset($data['object']['providerClass'])) {
            $this->classMetadata->setRepositoryClass($data['object']['providerClass']);
        }

        if (isset($data['object']['readDateConverter'])) {
            $this->classMetadata->setObjectReadDateFormat($data['object']['readDateConverter']);
        }

        if (isset($data['object']['writeDateConverter'])) {
            $this->classMetadata->setObjectWriteDateFormat($data['object']['writeDateConverter']);
        }

        if (isset($data['object']['propertyAccessStrategy'])) {
            $this->classMetadata->setPropertyAccessStrategyEnabled($data['object']['propertyAccessStrategy']);
        }

        if (isset($data['object']['fieldMappingExtensionClass'])) {
            $this->classMetadata->setPropertyMetadataExtensionClass($data['object']['fieldMappingExtensionClass']);
        }

        if (isset($data['object']['classMappingExtensionClass'])) {
            $this->classMetadata->setClassMetadataExtensionClass($data['object']['classMappingExtensionClass']);
        }

        if (isset($data['object']['customHydrator'])) {
            $this->classMetadata->setCustomHydrator($data['object']['customHydrator']);
        }

        if (isset($data['objectListeners'])) {
            $this->classMetadata->setObjectListenerClasses($data['objectListeners']);
        }

        if (isset($data['interceptors']['postExtract'])) {
            $this->classMetadata->setOnAfterExtract($data['interceptors']['postExtract']);
        }

        if (isset($data['interceptors']['postHydrate'])) {
            $this->classMetadata->setOnAfterHydrate($data['interceptors']['postHydrate']);
        }

        if (isset($data['interceptors']['preExtract'])) {
            $this->classMetadata->setOnBeforeExtract($data['interceptors']['preExtract']);
        }

        if (isset($data['interceptors']['preHydrate'])) {
            $this->classMetadata->setOnBeforeHydrate($data['interceptors']['preHydrate']);
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
        $includedFields = [];
        $excludedFields = [];
        $toOriginal = [];
        $toMapped = [];
        $dataSources = [];
        $providers = [];
        $valueObjects = [];
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $mappedTransientFieldNames = [];
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

            if (! isset($fieldData['class'])) {
                $fieldData['class'] = null;
            }

            $fieldDataByKey['field'] = $fieldData;

            if ('date' === $fieldData['type']) {
               $mappedDateFieldNames[] = $mappedFieldName;
            }

            if (isset($fieldData['writeConverter']) || isset($fieldData['readConverter'])) {

                $fieldsWithHydrationStrategy[$mappedFieldName] = [];
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = null;
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = null;
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTENSION_CLASS] = null;
            }

            if (isset($fieldData['writeConverter'])) {
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTRACTION_STRATEGY] = $fieldData['writeConverter'];
            }

            if (isset($fieldData['readConverter'])) {
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_HYDRATION_STRATEGY] = $fieldData['readConverter'];
            }

            if (isset($fieldData['mappingExtensionClass'])) {
                $fieldsWithHydrationStrategy[$mappedFieldName][ClassMetadata::INDEX_EXTENSION_CLASS] = $fieldData['mappingExtensionClass'];
            }

            if (isset($fieldData['dataSource'])) {
                $dataSources[$mappedFieldName] = $fieldData['dataSource'];
            }

            if (isset($fieldData['provider'])) {
                $providers[$mappedFieldName] = $fieldData['provider'];
            }

            if (isset($fieldData['valueObjects'])) {
                $valueObjects = $fieldData['valueObjects'];
            }

            $fieldsDataByKey[$mappedFieldName] = $fieldDataByKey;
        }

        if (isset($data['include'])) {
            foreach ($data['include'] as $fieldToInclude) {            
                $includedFields[$fieldToInclude] = true;
            }    
        }

        if (isset($data['exclude'])) {
            foreach ($data['exclude'] as $fieldToExclude) {            
                $excludedFields[$fieldToExclude] = true;
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

        if (count($fieldsWithHydrationStrategy)) {
            $this->classMetadata->setFieldsWithHydrationStrategy($fieldsWithHydrationStrategy);
        }
    }

    protected function parseContent($content)
    {
        return (new Parser())->parse($content);
    }
}