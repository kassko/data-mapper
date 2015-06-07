<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class RefDefaultSource
 *
 * @DM\RefDefaultSource(id="refDefaultSourceId")
 */
class RefDefaultSource
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
                    'refSource' => 'refDefaultSourceId'
                ]
            ]
        ];
    }
}
