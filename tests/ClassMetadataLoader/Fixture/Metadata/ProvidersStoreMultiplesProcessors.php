<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\ProvidersStore({
 *      @DM\Provider(
 *          id="personSource",
 *
 *          preprocessors = @DM\Methods({
 *              @DM\Method(method="somePrepocessorA"),
 *              @DM\Method(method="somePrepocessorB")
 *          }),
 *          processors = @DM\Methods({ 
 *              @DM\Method(method="someProcessorA"),
 *              @DM\Method(method="someProcessorB")
 *          }),
 *      )
 * })
 */
class ProvidersStoreMultiplesProcessors
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields' => [
                'mockField' => [
                    'name'     => 'mockFieldName',
                    'provider' => [
                        'id'                  => 'personSource',
                        'preprocessors'       => [
                          ['method' => 'somePrepocessorA'],
                          ['method' => 'somePrepocessorB']
                        ],
                        'processors'          => [
                          ['method' => 'someProcessorA'],
                          ['method' => 'someProcessorB']
                        ]
                    ]
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
    provider:
      id: personSource
      preprocessors:
        - method: somePrepocessorA
        - method: somePrepocessorB
      processors:
        - method: someProcessorA
        - method: someProcessorB
EOF;
    }
}
