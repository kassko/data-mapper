<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Metadata;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\ProvidersStore({
 *      @DM\Provider(
 *          id="personSource",
 *          depends={"#dependsFirst", "#dependsSecond", "#dependsThird"},
 *      )
 * })
 */
class ProvidersStoreMultiplesDepends
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
                        'id'    => 'personSource',
                        'class' => 'class',
                        'method'=> 'method',
                        'args'  => ['arg#1'],
                        'lazyLoading' => true,
                        'supplySeveralFields' => true,
                        'depends' => ['#dependsFirst', '#dependsSecond', '#dependsThird'],
                        'onFail' => 'checkException',
                        'exceptionClass' => '\RuntimeException',
                        'badReturnValue' => 'emptyArray',
                        'fallbackSourceId' => 'fallbackSourceId#1'
                    ]
                ]
            ]
        ];
    }
}
