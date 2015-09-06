<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class ValueObject
{
    /**
     * @DM\ValueObject(
     *      class="\ValueObjectClass",
     *      mappingResourceName="valueObjectResourceName",
     *      mappingResourcePath="valueObjectResourcePath",
     *      mappingResourceType="valueObjectResourceType"
     * )
     */
    protected $firstField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'firstField' => [
                    'name'         => 'firstFieldName',
                    'valueObjects' => [
                        'firstField'    => [
                            'class' => '\ValueObjectClass',
                            'mappingResourceName' => 'valueObjectResourceName',
                            'mappingResourcePath' => 'valueObjectResourcePath',
                            'mappingResourceType' => 'valueObjectResourceType'
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
fields:
  firstField:
    name: firstFieldName
  mockField:
    valueObjects:
      firstField:
        class: "\\\ValueObjectClass"
        mappingResourceName: valueObjectResourceName
        mappingResourcePath: valueObjectResourcePath
        mappingResourceType: valueObjectResourceType
EOF;
    }
}
