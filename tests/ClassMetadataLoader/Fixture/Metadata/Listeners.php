<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class Listeners
 * 
 * @DM\Listeners(
 *  preHydrate = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="preHydrateMethodName")   
 * }),
 *  postHydrate = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="postHydrateMethodName"),
 *      @DM\Method(class="SomeClassB", method="postHydrateMethodName") 
 * }),
 *  preExtract = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="preExtractMethodName", args="foo")   
 * }),
 *  postExtract = @DM\Methods({
 *      @DM\Method(class="SomeClass", method="postExtractMethodName", args={"foo", "#bar"})   
 * })
 * )
 */
class Listeners
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'listeners' => [
                'preHydrate' => ['class' => 'SomeClass', 'method' => 'preHydrateMethodName'],                
                'postHydrate' => 
                [
                    ['class' => 'SomeClass', 'method' => 'postHydrateMethodName'],
                    ['class' => 'SomeClassB', 'method' => 'postHydrateMethodName'],
                ], 
                'preExtract' => ['class' => 'SomeClass', 'method' => 'preExtractMethodName', 'args' => 'foo'],
                'postExtract' => ['class' => 'SomeClass', 'method' => 'postExtractMethodName', 'args' => ['foo', '#bar']],
            ]
        ];
    }

    /**
     * @return string
     */
    public static function loadInnerYamlMetadata()
    {
        return <<<EOF
listeners:
    preHydrate: 
        - {class: SomeClass, method: preHydrateMethodName}
    postHydrate: 
        - {class: SomeClass, method: postHydrateMethodName}
        - {class: SomeClassB, method: postHydrateMethodName}
    preExtract: 
        - {class: SomeClass, method: preExtractMethodName, args: foo}
    postExtract: 
        - {class: SomeClass, method: postExtractMethodName, args: ['foo', '#bar']}
EOF;
    }
}
