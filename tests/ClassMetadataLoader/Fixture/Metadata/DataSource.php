<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class DataSource
{
    /**
     * @DM\DataSource(
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
    protected $firstField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'firstField' => [
                    'name'       => 'originalFieldName',
                    'dataSource' => [
                        'id'                  => 'firstFieldId',
                        'lazyLoading'         => true,
                        'supplySeveralFields' => true,
                        'depends'             => ['depend#1', 'depend#2'],
                        'onFail'              => 'checkException',
                        'exceptionClass'      => '\RuntimeException',
                        'badReturnValue'      => 'emptyString',
                        'fallbackSourceId'    => 'firstFieldFallbackSourceId',
                        'preprocessor'        => ['method' => 'fooPreprocessor'],
                        'processor'           => ['method' => 'barProcessor'],                    
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
    name: originalFieldName
    dataSource:
      id: firstFieldId
      lazyLoading: true
      supplySeveralFields: true
      depends: [depend#1, depend#2]
      onFail: checkException
      exceptionClass: "\\\RuntimeException"
      badReturnValue: emptyString
      fallbackSourceId: firstFieldFallbackSourceId
      preprocessor:
        method: fooPreprocessor
      processor:
        method: barProcessor
      class: "\\\stdClass"
      method: someMethod
      args: [argument#1, argument#2]
EOF;
    }
}
