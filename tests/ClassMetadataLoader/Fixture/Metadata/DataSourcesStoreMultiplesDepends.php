<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource",
 *			depends={"#dependsFirst", "#dependsSecond", "#dependsThird"},
 *      )
 * })
 */
class DataSourcesStoreMultiplesDepends
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
                        'preprocessor'        => [
                            'class'  => '',
                            'method' => 'somePreprocessor',
                            'args'   => []
                        ],
                        'processor'           => [
                            'class'  => '',
                            'method' => 'someProcessor',
                            'args'   => []
                        ]
                    ]
                ]
            ]
        ];
    }
}
