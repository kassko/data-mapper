<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PostHydrate
 * 
 * @DM\PostHydrate(
 *      class="CustomHydratorClassName",
 *      method="postHydrateMethodName"
 * )
 */
class PostHydrate
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'interceptors'  => [
                'postHydrate'    => ['CustomHydratorClassName', 'postHydrateMethodName']
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
  postHydrate: [CustomHydratorClassName, postHydrateMethodName]
EOF;
    }
}
