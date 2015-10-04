<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Field
{
    /**
     * @DM\Field(
     *      name="FirstField",
     *      type="string",
     *      class="stdClass",
     *      readConverter="readConvertFirstField",
     *      writeConverter="writeConvertFirstField",
     *      fieldMappingExtensionClass="ExtensionClass"
     * )
     */
    protected $fieldOne;

    /**
     * @DM\Field(
     *      name="SecondField",
     *      type="integer",
     *      class="\DateTime",
     *      readDateConverter="readDateConvertSecondField",
     *      writeDateConverter="writeDateConvertSecondField",
     *      fieldMappingExtensionClass="ExtensionClass",
     *      defaultValue="12"
     * )
     */
    protected $fieldTwo;

    /**
     * @DM\Field(
     *      name="DateField",
     *      type="date"
     * )
     */
    protected $dateField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'fieldOne'  => [
                    'name'                       => 'FirstField',
                    'type'                       => 'string',
                    'class'                      => 'stdClass',
                    'readConverter'              => 'readConvertFirstField',
                    'writeConverter'             => 'writeConvertFirstField',
                    'fieldMappingExtensionClass' => 'ExtensionClass',
                ],
                'fieldTwo'  => [
                    'name'                       => 'SecondField',
                    'type'                       => 'integer',
                    'class'                      => '\DateTime',
                    'readDateConverter'          => 'readDateConvertSecondField',
                    'writeDateConverter'         => 'writeDateConvertSecondField',
                    'fieldMappingExtensionClass' => 'ExtensionClass',
                    'defaultValue'               => 12,
                ],
                'dateField' => [
                    'name'                       => 'DateField',
                    'type'                       => 'date',
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
  fieldOne:
    name: FirstField
    type: string
    class: stdClass
    readConverter: readConvertFirstField
    writeConverter: writeConvertFirstField
    fieldMappingExtensionClass: ExtensionClass
  fieldTwo:
    name: SecondField
    type: integer
    class: "\\\DateTime"
    readDateConverter: readDateConvertSecondField
    writeDateConverter: writeDateConvertSecondField
    fieldMappingExtensionClass: ExtensionClass
    defaultValue: 12
  dateField:
    name: DateField
    type: date
EOF;
    }
}
