<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Setter
{
    /**
     * @DM\Setter(
     *      prefix="setterPrefix",
     *      name="setterName"
     * )
     */
    protected $firstField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields'    => [
                'firstField'    => [
                    'name'      => 'firstField',
                    'setter'    => ['name' => 'setterName'],
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
        name: firstField
        setter: 
            name: setterName
EOF;
    }
}
