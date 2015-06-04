<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

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

}
