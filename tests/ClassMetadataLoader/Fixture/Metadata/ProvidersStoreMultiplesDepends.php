<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\ProvidersStore({
 *      @DM\Provider(
 *          id="personSource",
 *          depends={"#dependsFirst", "#dependsSecond", "#dependsThird"},
 *      )
 * })
 */
class ProvidersStoreMultiplesDepends
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields'    => [
                'mockField' => [
                    'name'      => 'mockFieldName',
                    'provider'  => [
                        'id'    => 'personSource',
                        'depends' => ['#dependsFirst', '#dependsSecond', '#dependsThird'],
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
  mockField:
    name: mockFieldName
    provider:
      id: personSource
      depends: [#dependsFirst, #dependsSecond, #dependsThird]
EOF;
    }
}
