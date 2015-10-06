<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class ExcludeImplicitSource
{
    protected $fieldToBindAutoToImplicitSource;

    protected $anotherFieldToBindAutoToImplicitSource;

    /**
     *@DM\ExcludeImplicitSource
     */
    protected $fieldNotToBindAutoToImplicitSource;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'fieldToBindAutoToImplicitSource' => [
                    'name'      => 'fieldToBindAutoToImplicitSource',
                ],
                'anotherFieldToBindAutoToImplicitSource' => [
                    'name'      => 'anotherFieldToBindAutoToImplicitSource',
                ],
                'fieldNotToBindAutoToImplicitSource' => [
                    'name'      => 'fieldNotToBindAutoToImplicitSource',
                ]
            ],
            'fieldsNotToBindToImplicitSource' => [
                'fieldNotToBindAutoToImplicitSource'
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
    fieldToBindAutoToImplicitSource:
        name: fieldToBindAutoToImplicitSource
    anotherFieldToBindAutoToImplicitSource:
        name: anotherFieldToBindAutoToImplicitSource
    fieldNotToBindAutoToImplicitSource:
        name: fieldNotToBindAutoToImplicitSource
fieldsNotToBindToImplicitSource: [fieldNotToBindAutoToImplicitSource]        
EOF;
    }
}