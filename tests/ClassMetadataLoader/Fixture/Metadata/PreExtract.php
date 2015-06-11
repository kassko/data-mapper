<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PreExtract
 * 
 * @DM\PreExtract(
 *      class="CustomHydratorClassName",
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
                'preExtract'    => ['CustomHydratorClassName', 'preExtractMethodName']
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
  preExtract: [CustomHydratorClassName, preExtractMethodName]
EOF;
    }
}
