<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\Object(fieldExclusionPolicy="exclude_all")
 */
class ToInclude
{
    /**
     * @DM\ToInclude
     */
    protected $includedField;

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
            'fieldExclusionPolicy' => 'exclude_all',
            'fieldsToInclude' => [
                'includedField'
            ],
            'fields'  => [
                'includedField' => [
                    'name'     => 'includedField'
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
object:        
  fieldExclusionPolicy: exclude_all # Test if the deprecated key "object.fieldExclusionPolicy" (now "fieldExclusionPolicy") still works.       
fieldsToInclude: [includedField]
fields:
  includedField:
    name: includedField
  field:
    name: field
EOF;
    }
}
