<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\ProvidersStore({
 *      @DM\Provider(
 *          id="personSource",
 *
 *          preprocessors = @DM\Methods({
 *              @DM\Method(method="somePrepocessorA"),
 *              @DM\Method(method="somePrepocessorB")
 *          }),
 *          processors = @DM\Methods({ 
 *              @DM\Method(method="someProcessorA"),
 *              @DM\Method(method="someProcessorB")
 *          }),
 *      )
 * })
 */
class ProvidersStoreMultiplesProcessors
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'mockField' => [
                    'name'     => 'mockFieldName',
                    'provider' => [
                        'id'                  => 'personSource',
                        'class'               => 'class',
                        'method'              => 'method',
                        'args'                => ['arg#1'],
                        'lazyLoading'         => true,
                        'supplySeveralFields' => true,
                        'depends'             => [],
                        'onFail'              => 'checkException',
                        'exceptionClass'      => '\RuntimeException',
                        'badReturnValue'      => 'emptyArray',
                        'fallbackSourceId'    => 'fallbackSourceId#1',
                        'preprocessors'       => [
                            'items' => [
                                [
                                    'method' => 'somePrepocessorA',
                                    'class'  => '##this',
                                    'args'   => []
                                ],
                                [
                                    'method' => 'somePrepocessorB',
                                    'class'  => '##this',
                                    'args'   => []
                                ]
                            ]
                        ],
                        'processors'          => [
                            'items' => [
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
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public static function loadInnerYamlMetadata()
    {
        return <<<EOF
fields:
  mockField:
    name: mockFieldName
    provider:
      id: personSource
      class: class
      method: method
      args: [arg#1]
      lazyLoading: true
      supplySeveralFields: true
      depends: []
      onFail: checkException
      exceptionClass: "\\\RuntimeException"
      badReturnValue: emptyArray
      fallbackSourceId: fallbackSourceId#1
      preprocessors:
        items:
          - method: somePrepocessorA
            class: "##this"
            args: []
          - method: somePrepocessorB
            class: "##this"
            args: []
      processors:
        items:
          - method: someProcessorA
            class: "##this"
            args: []
          - method: someProcessorB
            class: "##this"
            args: []
EOF;
    }
}
