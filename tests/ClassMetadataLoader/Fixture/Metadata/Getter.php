<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Getter
{
    /**
     * @DM\Getter(
     *      name="getterName"
     * )
     */
    protected $firstField;

    /**
     * @DM\Getter(
     *      prefix="is",
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
                    'name'      => 'firstField',
                    'getter'    => ['name' => 'getterName'],
                ],
                'secondField'    => [
                    'name'      => 'secondField',
                    'getter'    => ['prefix' => 'is'],
                ],
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
        getter: 
            name: getterName
    secondField:
        name: secondField
        getter:
            prefix: is
EOF;
    }
}
