<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class ExcludeDefaultSource
{
    protected $fieldToBindAutoToDefaultSource;

    protected $anotherFieldToBindAutoToDefaultSource;

    /**
     *@DM\ExcludeDefaultSource
     */
    protected $fieldNotToBindAutoToDefaultSource;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'fieldToBindAutoToDefaultSource' => [
                    'name'      => 'fieldToBindAutoToDefaultSource',
                ],
                'anotherFieldToBindAutoToDefaultSource' => [
                    'name'      => 'anotherFieldToBindAutoToDefaultSource',
                ],
                'fieldNotToBindAutoToDefaultSource' => [
                    'name'      => 'fieldNotToBindAutoToDefaultSource',
                ]
            ],
            'fieldsNotToBindToDefaultSource' => [
                'fieldNotToBindAutoToDefaultSource'
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
    fieldToBindAutoToDefaultSource:
        name: fieldToBindAutoToDefaultSource
    anotherFieldToBindAutoToDefaultSource:
        name: anotherFieldToBindAutoToDefaultSource
    fieldNotToBindAutoToDefaultSource:
        name: fieldNotToBindAutoToDefaultSource
fieldsNotToBindToDefaultSource: [fieldNotToBindAutoToDefaultSource]        
EOF;
    }
}