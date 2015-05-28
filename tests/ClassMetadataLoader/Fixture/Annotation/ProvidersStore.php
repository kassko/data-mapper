<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

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
    public function method(){}
}
