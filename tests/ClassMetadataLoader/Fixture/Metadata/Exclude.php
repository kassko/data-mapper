<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Exclude
{
    /**
     * @DM\Exclude
     */
    protected $excludedField;

    /**
     * @DM\Field
     */
    protected $field;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'exclude' => [
                'excludedField'
            ],
            'fields'  => [
                'excludedField' => [
                    'name'     => 'originalFieldName'
                ],
                'field'         => [
                    'name'     => 'field'
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
exclude: [excludedField]
fields:
  excludedField:
    name: originalFieldName
  field:
    name: field
EOF;
    }
}
