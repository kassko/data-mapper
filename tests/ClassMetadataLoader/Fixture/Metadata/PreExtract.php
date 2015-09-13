<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PreExtract
 * 
 * @DM\PreExtract(
 *      method="preExtractMethodName"
 * )
 */
class PreExtract
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'interceptors'  => [
                'preExtract'    =>  ['method' => 'preExtractMethodName']
            ]
        ];
    }

    /**
     * @return string
     */
    public static function loadInnerYamlMetadata()
    {
        return <<<EOF
interceptors:
  preExtract: 
    method: preExtractMethodName
EOF;
    }
}
