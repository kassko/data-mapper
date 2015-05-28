<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 * 
 * @DM\PreHydrate(
 *      class="CustomHydratorClassName",
 *      method="preHydrateMethodName"
 * )
 */
class PreHydrate
{
}
