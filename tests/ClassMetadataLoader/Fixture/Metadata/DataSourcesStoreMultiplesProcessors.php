<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
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
           method: somePrepocessorA
           args: []
         - class: "##this"
           method: somePrepocessorB
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
