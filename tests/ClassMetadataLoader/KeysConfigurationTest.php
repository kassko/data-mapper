<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader\ArrayLoader;
use Kassko\DataMapper\ClassMetadataLoader\KeysConfiguration;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class KeysConfigurationTest
 * 
 * @author kko
 */
class KeysConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider someDataProvider
     */
    public function one($data)
    {
        //$data = ['root' => $data];

        //ArrayLoader::normalize($data);

        $this->assertTrue(true);
    }

    public function someDataProvider()
    {
        return [
            [
                [
                    'fields' => ['fieldOne' => null]
                ]
            ],
        ];
        return [
            [[]],
            [
                [
                    'fields' => ['fieldOne' => []]
                ]
            ],
            [
                [
                    'fields' => ['fieldOne' => ['name' => 'foo']]
                ]
            ],
            [
                [
                    'fields' => ['fieldOne' => ['name' => 'foo'], ['dataSource' => []]]
                ]
            ],
            [
                [
                    'object'    => 
                    [
                        'fieldExclusionPolicy'  => 'exclude_all',
                        'providerClass'         => 'testProviderClass',
                        'readDateConverter'     => 'testReadDateConverter',
                        'writeDateConverter'    => 'testWriteDateConverter',
                        'propertyAccessStrategy'=> true,
                        'fieldMappingExtensionClass' => 'testFieldMappingExtensionClass',
                        'classMappingExtensionClass' => 'testClassMappingExtensionClass',

                        'dataSourcesStore'    => 
                        [
                            [
                                'id'=> 'personSource',
                                'class'=> 'Kassko\Sample\PersonDataSource',
                                'method'=> 'getData',
                                'args' => ['#id'],
                                'lazyLoading' => true,
                                'supplySeveralFields' => true,
                                'onFail'    => 'checkException',
                                'exceptionClass' => '\RuntimeException',
                                'badReturnValue' => 'emptyString',
                                'fallbackSourceId' => 'testFallbackSourceId',
                                'depends' => ['#dependsFirst'],
                                'preprocessor' => [
                                    'class' => '',
                                    'method' => 'somePreprocessor',
                                    'args' => []
                                ],
                                'processor' => [
                                    'class' => '',
                                    'method' => 'someProcessor',
                                    'args' => []
                                ],
                                'preprocessors'       => [//todo: normalize to remove dimension "items" in under preprocessors
                                    [
                                        'method' => 'somePreprocessorA',
                                        'class'  => '##this',
                                        'args'   => []
                                    ],
                                    [
                                        'method' => 'somePreprocessorB',
                                        'class'  => '##this',
                                        'args'   => []
                                    ]
                                ],
                                'processors'          => [//todo: idem
                                    [
                                        'method' => 'someProcessorA',
                                        'class'  => '##this',
                                        'args'   => []
                                    ],
                                    [
                                        'method' => 'someProcessorB',
                                        'class'  => '##this',
                                        'args'   => []
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'fields'    => [
                        'firstField'    => [
                            'name'      => 'firstField',
                            'getter'    => ['name' => 'getterName'],
                        ],
                        'secondField'    => [
                            'name'      => 'secondField',
                            'getter'    => ['prefix' => 'is'],
                        ],
                        'fieldOne'  => [
                            'name'                       => 'FirstField',
                            'type'                       => 'string',
                            'class'                      => 'stdClass',
                            'readConverter'              => 'readConvertFirstField',
                            'writeConverter'             => 'writeConvertFirstField',
                            'fieldMappingExtensionClass' => 'ExtensionClass',
                        ],
                        'fieldTwo'  => [
                            'name'                       => 'SecondField',
                            'type'                       => 'integer',
                            'class'                      => '\DateTime',
                            'readDateConverter'          => 'readDateConvertSecondField',
                            'writeDateConverter'         => 'writeDateConvertSecondField',
                            'fieldMappingExtensionClass' => 'ExtensionClass',
                            'defaultValue'               => 12
                        ],
                        'dateField' => [
                            'name'                       => 'DateField',
                            'type'                       => 'date'
                        ]
                    ],
                    'include' => [
                        'includedField'
                    ],
                    'exclude' => [
                        'excludedField'
                    ],
                    'refDefaultSource' => 'refDefaultSourceId',
                    'listeners' => [
                        'preHydrate' => ['class' => 'SomeClass', 'method' => 'preHydrateMethodName'],                
                        'postHydrate' => 
                        [
                            ['class' => 'SomeClass', 'method' => 'postHydrateMethodName'],
                            ['class' => 'SomeClassB', 'method' => 'postHydrateMethodName'],
                        ], 
                        'preExtract' => ['class' => 'SomeClass', 'method' => 'preExtractMethodName', 'args' => 'foo'],
                        'postExtract' => ['class' => 'SomeClass', 'method' => 'postExtractMethodName', 'args' => ['foo', '#bar']],
                    ],
                    'objectListeners'   => ['SomeListenerAClass', 'SomeListenerBClass'],
                    'config' => [
                        'firstField'    => [
                            'class' => '\ValueObjectClass',
                            'mappingResourceName' => 'valueObjectResourceName',
                            'mappingResourcePath' => 'valueObjectResourcePath',
                            'mappingResourceType' => 'valueObjectResourceType'
                        ]
                    ],
                    'valueObject' => [
                        'firstField'    => [
                            'class' => '\ValueObjectClass',
                            'mappingResourceName' => 'valueObjectResourceName',
                            'mappingResourcePath' => 'valueObjectResourcePath',
                            'mappingResourceType' => 'valueObjectResourceType'
                        ]
                    ],
                    'id'      => 'firstField',
                    'idComposite'   => ['firstField', 'secondField'],
                    'transient' => ['firstField'],
                    'version'   => 'firstField',
                ]
            ],
        ];
    }
}
