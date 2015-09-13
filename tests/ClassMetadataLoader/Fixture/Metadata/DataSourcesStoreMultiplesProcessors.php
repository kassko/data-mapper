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
                        'preprocessors'       => [
                            ['method' => 'somePreprocessorA'],
                            ['method' => 'somePreprocessorB']
                        ],
                        'processors'          => [
                            ['method' => 'someProcessorA'],
                            ['method' => 'someProcessorB']                            
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
     preprocessors:
      - method: somePreprocessorA
      - method: somePreprocessorB
     processors:
      - method: someProcessorA
      - method: someProcessorB
EOF;
    }
}
