<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class PreHydrate
 * 
 * @DM\PreHydrate(
 *      class="CustomHydratorClassName",
 *      method="preHydrateMethodName"
 * )
 */
class PreHydrate
{
}
