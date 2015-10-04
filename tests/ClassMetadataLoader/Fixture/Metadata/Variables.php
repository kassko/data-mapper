<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

class Variables
{
    /**
     * @DM\Variables({"var_a" = "foo", "var_b" = "123"})
     */
    protected $firstField;

    protected $secondField;

    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'firstField' => [
                    'variables' => [
                        'var_a' => 'foo',
                        'var_b' => '123'
                    ]
                ],
                'secondField'
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
    firstField:
        variables:
            var_a: foo
            var_b: 123
    secondField: ~
EOF;
    }
}
