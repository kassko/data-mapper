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
          depends: [#dependsFirst, #dependsSecond, #dependsThird]
EOF;
    }
}
