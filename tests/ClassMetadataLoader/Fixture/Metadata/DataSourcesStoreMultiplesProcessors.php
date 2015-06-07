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
}
