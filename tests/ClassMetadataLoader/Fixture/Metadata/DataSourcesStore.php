<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource",
 *          class="Kassko\Sample\PersonDataSource",
 *          method="getData",
 *          args="#id",
 *          lazyLoading=true,
 *          supplySeveralFields=true,
 *          onFail="checkException",
 *          exceptionClass="\RuntimeException",
 *          badReturnValue="emptyString",
 *          fallbackSourceId="testFallbackSourceId",
 *          depends="#dependsFirst",
 *
 *          preprocessor = @DM\Method(method="somePreprocessor"),
 *          processor = @DM\Method(method="someProcessor"),
 *      )
 * })
 */
class DataSourcesStore
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'object'    => [
                'dataSourcesStore'    => [
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
     depends: [#dependsFirst]
     preprocessor:
       class: ""
       method: somePreprocessor
       args: []
     processor:
       class: ""
       method: someProcessor
       args: []
EOF;
    }
}
