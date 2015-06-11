<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 *
 * @DM\ProvidersStore({
 *      @DM\Provider(
 *          id="providers#1",
 *          lazyLoading=true,
 *          supplySeveralFields=true,
 *          depends={"depend#1"},
 *          onFail="checkException",
 *          exceptionClass="\RuntimeException",
 *          badReturnValue="emptyArray",
 *          fallbackSourceId="fallbackSourceId#1",
 *          class="class",
 *          method="method",
 *          args={"arg#1"}
 *      )
 * })
 */
class ProvidersStore
{
    /**
     * @return array
     */
    public static function loadInnerPhpMetadata()
    {
        return [
            'fields'    => [
                'mockField' => [
                    'name'      => 'mockFieldName',
                    'provider'  => [
                        'id'    => 'providers#1',
                        'class' => 'class',
                        'method'=> 'method',
                        'args'  => ['arg#1'],
                        'lazyLoading' => true,
                        'supplySeveralFields' => true,
                        'depends' => ['depend#1'],
                        'onFail' => 'checkException',
                        'exceptionClass' => '\RuntimeException',
                        'badReturnValue' => 'emptyArray',
                        'fallbackSourceId' => 'fallbackSourceId#1'
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
      id: providers#1
      class: class
      method: method
      args: [arg#1]
      lazyLoading: true
      supplySeveralFields: true
      depends: [depend#1]
      onFail: checkException
      exceptionClass: "\\\RuntimeException"
      badReturnValue: emptyArray
      fallbackSourceId: fallbackSourceId#1
EOF;
    }
}
