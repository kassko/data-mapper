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
                    'name'     => 'excludedField'
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
exclude: [excludedField] # Test if the deprecated key "exclude" (now "fieldsToExclude") still works.
fields:
  excludedField:
    name: excludedField
  field:
    name: field
EOF;
    }
}
