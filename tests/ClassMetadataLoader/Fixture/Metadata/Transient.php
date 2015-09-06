<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Transient
{
    /**
     * @DM\Transient
     */
    protected $firstField;

    /**
     * @var string
     */
    protected $secondField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'transient' => ['firstField'],
            'fields'    => [
                'firstField'    => [
                    'name'      => 'firstFieldName'
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
transient: [firstField]
fields:
  firstField:
    name: firstFieldName
EOF;
    }
}
