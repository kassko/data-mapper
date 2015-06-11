<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Version
{
    /**
     * @DM\Version
     */
    protected $firstField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'version'   => 'firstField',
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
version: firstField
fields:
  firstField:
    name: firstFieldName
EOF;
    }
}
