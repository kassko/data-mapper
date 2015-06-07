<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Provider
{
    /**
     * @DM\Provider(
     *      id="providerFieldId",
     *      lazyLoading=true,
     *      supplySeveralFields=true,
     *      depends={"depend#1","depend#2"},
     *      onFail="checkException",
     *      exceptionClass="\RuntimeException",
     *      badReturnValue="emptyString",
     *      fallbackSourceId="firstFieldFallbackSourceId",
     *      preprocessor=@DM\Method(method="fooPreprocessor"),
     *      processor=@DM\Method(method="barProcessor"),
     *      class="\stdClass",
     *      method="someMethod",
     *      args={"argument#1", "argument#2"}
     * )
     */
    protected $providerField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'providerField' => [
                    'name'                => 'providerFieldName',
                    'provider'            => [
                        'id'                  => 'providerFieldId',
                        'lazyLoading'         => true,
                        'supplySeveralFields' => true,
                        'depends'             => ['depend#1', 'depend#2'],
                        'onFail'              => 'checkException',
                        'exceptionClass'      => '\RuntimeException',
                        'badReturnValue'      => 'emptyString',
                        'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                        'preprocessor'        => [
                            'class'  => '##this',
                            'method' => 'fooPreprocessor',
                            'args'   => []
                        ],
                        'processor'           => [
                            'class'  => '##this',
                            'method' => 'barProcessor',
                            'args'   => []
                        ],
                        'preprocessors'       => [],
                        'processors'          => [],
                        'class'               => '\stdClass',
                        'method'              => 'someMethod',
                        'args'                => ['argument#1', 'argument#2']
                    ]
                ]
            ]
        ];
    }
}