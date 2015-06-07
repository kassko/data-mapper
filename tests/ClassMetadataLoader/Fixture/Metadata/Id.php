<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Id
{
    /**
     * @DM\Id
     */
    protected $firstField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'id'        => 'firstField',
            'fields'    => [
                'firstField'    => [
                    'name'      => 'firstFieldName'
                ]
            ]
        ];
    }
}
