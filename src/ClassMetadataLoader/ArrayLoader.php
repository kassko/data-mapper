<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\ClassMetadata\Model;
use Symfony\Component\Config\Definition\Processor;

/**
 * Base for class metadata loader that parse data to array before loading them.
 *
 * @author kko
 */
abstract class ArrayLoader extends AbstractLoader
{
    private $classMetadata;

    protected function doLoadClassMetadata(ClassMetadata $classMetadata, array $data)
    {
        $this->normalize($data);

        $this->classMetadata = $classMetadata;
        $this->loadData($data);

        return $this->classMetadata;
    }

    /**
     * Transforms the format to a convenient format.
     * Enforce undefined keys to be defined and setted to a default value (Usually the value "null").
     *
     * @param array $data The input data to reformat.
     */
    protected function normalize(array &$data)
    {
        /**
         * Transforms 'fieldA' to ['fieldA' => ['name' => 'fieldA']]
         * and so will transforms ['fieldA', 'fieldB'] 
         * to [['fieldA' => ['name' => 'fieldA']], ['fieldB' => ['name' => 'fieldB']]].
         *
         * Transforms ['fieldA' => null] to ['fieldA' => ['name' => 'fieldA']].
         *
         * Transforms ['fieldA' => []] to ['fieldA' => ['name' => 'fieldA']].
         *
         * Transforms ['fieldA' => 'fieldARealName'] to ['fieldA' => ['name' => 'fieldARealName']].
         */
        $dataName = 'fields';
        if (isset($data[$dataName])) {
            $normalizedFieldsData = [];

            foreach ($data[$dataName] as $mappedFieldName => $fieldData) {

                if (! isset($fieldData['name'])) {
                    if (is_scalar($fieldData)) {
                        //if $fieldData is a scalar value and a not null value, it contains the field name.
                        $mappedFieldName = $fieldData;
                        $fieldData = [];
                    } elseif (is_null($fieldData)) {
                        $fieldData = [];  
                    }
                    $normalizedFieldsData[$mappedFieldName] = array_merge($fieldData, ['name' => $mappedFieldName]);
                    $data[$dataName] = $normalizedFieldsData;
                    //Otherwise, the key contains the good value for $mappedFieldName.
                }   
            }    
        }

        $processor = new Processor();
        $keysConfiguration = new KeysConfiguration();
        $data = $processor->processConfiguration($keysConfiguration, [$data]);
    }

    protected function loadData(array $data)
    {
        $this->loadClassData($data);
        $this->loadFieldData($data);
    }

    private function loadClassData(array $data)
    {
        if (isset($data['object']['dataSourcesStore'])) {

            $dataSourcesStore = [];
            foreach ($data['object']['dataSourcesStore'] as $dataSource) {
                $sourceModel = new Model\DataSource;
                $dataSourcesStore[] = $this->loadSource($sourceModel, $dataSource);
            }
            $this->classMetadata->setDataSourcesStore($dataSourcesStore);
        }
        
        if (isset($data['object']['providersStore'])) {

            $providersStore = [];
            foreach ($data['object']['providersStore'] as $provider) {
                $sourceModel = new Model\DataSource;
                $providersStore[] = $this->loadSource($sourceModel, $provider);
            }
            $this->classMetadata->setProvidersStore($providersStore);
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

        if (isset($data['refImplicitSource'])) {
            $this->classMetadata->setRefImplicitSource($data['refImplicitSource']);
        }

        if (isset($data['listeners']['preHydrate'])) {
            foreach ($data['listeners']['preHydrate'] as $listenerConfig) {
                $args = [];
                if (isset($listenerConfig['args'])) {
                    $args = (array)$listenerConfig['args'];  
                }

                $this->classMetadata->addPreHydrateListener(
                    new Model\Method($listenerConfig['class'], $listenerConfig['method'], $args)
                );
            }
        }

        if (isset($data['listeners']['postHydrate'])) {
            foreach ($data['listeners']['postHydrate'] as $listenerConfig) {
                $args = [];
                if (isset($listenerConfig['args'])) {
                    $args = (array)$listenerConfig['args'];  
                }

                $this->classMetadata->addPostHydrateListener(
                    new Model\Method($listenerConfig['class'], $listenerConfig['method'], $args)
                );
            }
        }

        if (isset($data['listeners']['preExtract'])) {
            foreach ($data['listeners']['preExtract'] as $listenerConfig) {
                $args = [];
                if (isset($listenerConfig['args'])) {
                    $args = (array)$listenerConfig['args'];  
                }

                $this->classMetadata->addPreExtractListener(
                    new Model\Method($listenerConfig['class'], $listenerConfig['method'], $args)
                );
            }
        }

        if (isset($data['listeners']['postExtract'])) {
            foreach ($data['listeners']['postExtract'] as $listenerConfig) {
                $args = [];
                if (isset($listenerConfig['args'])) {
                    $args = (array)$listenerConfig['args'];  
                }

                $this->classMetadata->addPostExtractListener(
                    new Model\Method($listenerConfig['class'], $listenerConfig['method'], $args)
                );
            }
        }

        /**
         * The 'interceptor' key is to be removed because replaced by listeners.
         */
        if (isset($data['interceptors']['preHydrate'])) {
            $this->classMetadata->setOnBeforeHydrate($data['interceptors']['preHydrate']['method']);
        }
        if (isset($data['interceptors']['postHydrate'])) {
            $this->classMetadata->setOnAfterHydrate($data['interceptors']['postHydrate']['method']);
        }
        if (isset($data['interceptors']['preExtract'])) {
            $this->classMetadata->setOnBeforeExtract($data['interceptors']['preExtract']['method']);
        }
        if (isset($data['interceptors']['postExtract'])) {
            $this->classMetadata->setOnAfterExtract($data['interceptors']['postExtract']['method']);
        }
        
        if (isset($data['objectListeners'])) {
            $this->classMetadata->setObjectListenerClasses($data['objectListeners']);
        } 

        if (isset($data['fieldExclusionPolicy'])) {
            $this->classMetadata->setFieldExclusionPolicy($data['fieldExclusionPolicy']);
        }

        $fieldsToExclude = [];
        if (isset($data['fieldsToExclude'])) {
            $fieldsToExclude = $data['fieldsToExclude'];    
        } elseif (isset($data['exclude'])) {
            $fieldsToExclude = $data['exclude'];    
        }

        foreach ($fieldsToExclude as $fieldToExclude) {            
            $fieldsToExclude[$fieldToExclude] = true;
        }

        if (count($fieldsToExclude)) {
            $this->classMetadata->setExcludedFields($fieldsToExclude);
        }

        $fieldsToInclude = [];
        if (isset($data['fieldsToInclude'])) {
            $fieldsToInclude = $data['fieldsToInclude'];    
        } elseif (isset($data['include'])) {
            $fieldsToInclude = $data['include'];    
        }

        foreach ($fieldsToInclude as $fieldToInclude) {            
            $fieldsToInclude[$fieldToInclude] = true;
        }    

        if (count($fieldsToInclude)) {
            $this->classMetadata->setIncludedFields($fieldsToInclude);
        }

        if (isset($data['fieldsNotToBindToImplicitSource'])) {
            $fieldsNotToBindToImplicitSource = array_fill_keys($data['fieldsNotToBindToImplicitSource'], true);
            $this->classMetadata->setFieldsNotToBindAutoToImplicitSource($fieldsNotToBindToImplicitSource);
        }
    }

    private function loadFieldData(array $data)
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
        $refSources = [];
        $config = [];
        $variables = [];
        $mappedIdFieldName = null;
        $mappedIdCompositePartFieldName = [];
        $mappedVersionFieldName = null;
        $mappedTransientFieldNames = [];
        $fieldsWithHydrationStrategy = [];
        $getters = [];
        $setters = [];

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

        if (isset($data['fields'])) {
            $dataName = 'fields';
            foreach ($data[$dataName] as $mappedFieldName => $fieldData) {

                if (! is_array($fieldData)) {
                    $fieldData = ['name' => $fieldData];
                }

                if (! isset($fieldData['type'])) {
                    $fieldData['type'] = 'string';
                }

                if (! isset($fieldData['class'])) {
                    $fieldData['class'] = null;
                }

                $mappedFieldNames[] = $mappedFieldName;
                $originalFieldNames[] = $fieldData['name'];

                $toOriginal[$mappedFieldName] = $fieldData['name'];
                $toMapped[$fieldData['name']] = $mappedFieldName;

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
                    $sourceModel = new Model\DataSource;
                    $dataSources[$mappedFieldName] = $this->loadSource($sourceModel, $fieldData['dataSource']);
                }

                if (isset($fieldData['provider'])) {
                    $sourceModel = new Model\Provider;
                    $providers[$mappedFieldName] = $this->loadSource($sourceModel, $fieldData['provider']);
                }

                if (isset($fieldData['refSource'])) {
                    $refSources[$mappedFieldName] = $fieldData['refSource'];
                }

                if (! empty($fieldData['config'])) {
                    $config[$mappedFieldName] = $fieldData['config'];
                }

                if (! empty($fieldData['variables'])) {
                    $variables[$mappedFieldName] = $fieldData['variables'];
                }

                if (isset($fieldData['getter'])) {
                    $getters[$mappedFieldName] = $fieldData['getter'];
                }

                if (isset($fieldData['setter'])) {
                    $setters[$mappedFieldName] = $fieldData['setter'];
                }

                $fieldsDataByKey[$mappedFieldName] = $fieldDataByKey;
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

        if (count($dataSources)) {
            $this->classMetadata->setDataSources($dataSources);
        }

        if (count($providers)) {
            $this->classMetadata->setProviders($providers);
        }

        if (count($refSources)) {
            $this->classMetadata->setRefSources($refSources);
        }

        if (count($config)) {
            $this->classMetadata->setValueObjects($config);
        }

        if (count($variables)) {
            $this->classMetadata->setVariables($variables);
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

        if (count($getters)) {
            $this->classMetadata->setGetters($getters);
        }

        if (count($setters)) {
            $this->classMetadata->setSetters($setters);
        }
    }

    /**
     * Hydrate a source model from raw datas.
     * 
     * @param Model\Source $sourceModel
     * @param array $sourceData
     *
     * @return Model\Source
     */
    private function loadSource(Model\Source $sourceModel, array $sourceData)
    {
        $sourceModel
        ->setId($sourceData['id'])
        ->setMethod(new Model\Method($sourceData['class'], $sourceData['method'], $sourceData['args']))
        ->setLazyLoading($sourceData['lazyLoading'])
        ->setSupplySeveralFields($sourceData['supplySeveralFields'])
        ->setOnFail($sourceData['onFail'])
        ->setExceptionClass($sourceData['exceptionClass'])
        ->setBadReturnValue($sourceData['badReturnValue'])
        ->setFallbackSourceId($sourceData['fallbackSourceId'])
        ->setDepends($sourceData['depends'])
        ;

        if (isset($sourceData['preprocessor']['method'])) {
            $sourceModel->addPreprocessor(
                new Model\Method(
                    $sourceData['preprocessor']['class'],
                    $sourceData['preprocessor']['method'],
                    $sourceData['preprocessor']['args']
                )
            );
        } elseif (isset($sourceData['preprocessors'])) {
            foreach ($sourceData['preprocessors'] as $preprocessor) {
                $sourceModel->addPreprocessor(
                    new Model\Method(
                        $preprocessor['class'],
                        $preprocessor['method'],
                        $preprocessor['args']
                    )
                );
            }
        }

        if (isset($sourceData['processor']['method'])) {
            $sourceModel->addProcessor(
                new Model\Method(
                    $sourceData['processor']['class'],
                    $sourceData['processor']['method'],
                    $sourceData['processor']['args']
                )
            );
        } elseif (isset($sourceData['processors'])) {
            foreach ($sourceData['processors'] as $processor) {
                $sourceModel->addProcessor(
                    new Model\Method(
                        $processor['class'],
                        $processor['method'],
                        $processor['args']
                    )
                );
            }
        }

        return $sourceModel;
    }
}
