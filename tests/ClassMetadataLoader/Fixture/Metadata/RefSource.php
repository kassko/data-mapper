<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class RefSource
{
    /**
     * @DM\RefSource(
     *      id="firstFieldId",
     *      ref="firstFieldRef"
     * )
     */
    protected $firstField;

    /**
     * @DM\Provider(
     *      id="firstFieldId",
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
    protected $secondField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields'    => [
                'firstField'    => [
                    'name'      => 'firstFieldName',
                    'refSource' => 'firstFieldId'
                ],
                'secondField'   => [
                    'name'                => 'providerFieldName',
                    'provider'            => [
                        'id'                  => 'firstFieldId',
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

    /**
     * @return string
     */
    public static function loadInnerYamlMetadata()
    {
        return <<<EOF
fields:
  firstField:
    name: firstFieldName
    refSource: firstFieldId
  secondField:
    name: providerFieldName
    provider:
      id: firstFieldId
      lazyLoading: true
      supplySeveralFields: true
      depends: [depend#1, depend#2]
      onFail: checkException
      exceptionClass: "\\\RuntimeException"
      badReturnValue: emptyString
      fallbackSourceId: firstFieldFallbackSourceId
      preprocessor:
        class: "##this"
        method: fooPreprocessor
        args: []
      processor:
        class: "##this"
        method: barProcessor
        args: []
      preprocessors: []
      processors: []
      class: "\\\stdClass"
      method: someMethod
      args: [argument#1, argument#2]
EOF;
    }
}
