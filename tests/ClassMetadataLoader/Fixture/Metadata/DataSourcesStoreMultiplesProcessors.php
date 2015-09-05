<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource",
 *
 *          preprocessors = @DM\Methods({
 *              @DM\Method(method="somePreprocessorA"),
 *              @DM\Method(method="somePreprocessorB")
 *          }),
 *          processors = @DM\Methods({ 
 *              @DM\Method(method="someProcessorA"),
 *              @DM\Method(method="someProcessorB")
 *          }),
 *      )
 * })
 */
class DataSourcesStoreMultiplesProcessors
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'object' => [
                'dataSourcesStore' => [
                    [
                        'id'                  => 'personSource',
                        'depends'             => [
                            '#dependsFirst', '#dependsSecond', '#dependsThird'
                        ],
                        'class'               => 'Kassko\Sample\PersonDataSource',
                        'method'              => 'getData',
                        'args'                => ['#id'],
                        'lazyLoading'         => true,
                        'supplySeveralFields' => true,
                        'onFail'              => 'checkException',
                        'exceptionClass'      => '\RuntimeException',
                        'badReturnValue'      => 'emptyString',
                        'fallbackSourceId'    => 'testFallbackSourceId',
                        'preprocessors'       => [
                            'items' => [
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
object:
  dataSourcesStore:
   - id: personSource
     class: "Kassko\\\Sample\\\PersonDataSource"
     method: getData
     args: [#id]
     lazyLoading: true
     supplySeveralFields: true
     onFail: checkException
     exceptionClass: \RuntimeException
     badReturnValue: emptyString
     fallbackSourceId: testFallbackSourceId
     depends: [#dependsFirst, #dependsSecond, #dependsThird]
     preprocessors:
       items:
         - class: "##this"
           method: somePreprocessorA
           args: []
         - class: "##this"
           method: somePreprocessorB
           args: []
     processors:
       items:
         - class: "##this"
           method: someProcessorA
           args: []
         - class: "##this"
           method: someProcessorB
           args: []
EOF;
    }
}
