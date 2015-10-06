<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class RefImplicitSource
 *
 * @DM\RefImplicitSource(id="refImplicitSourceId")
 */
class RefImplicitSource
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'mockField' => [
                    'name'      => 'mockFieldName',
                    'refSource' => 'refImplicitSourceId'
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
  mockField:
    name: mockFieldName
    refSource: refImplicitSourceId
EOF;
    }
}
