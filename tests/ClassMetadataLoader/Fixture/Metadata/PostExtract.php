<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PostExtract
 * 
 * @DM\PostExtract(
 *      class="CustomHydratorClassName",
 *      method="postExtractMethodName"
 * )
 */
class PostExtract
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'interceptors'  => [
                'postExtract'    => ['class' => 'CustomHydratorClassName', 'method' => 'postExtractMethodName']
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
  postExtract: 
    class: CustomHydratorClassName
    method: postExtractMethodName
EOF;
    }
}
