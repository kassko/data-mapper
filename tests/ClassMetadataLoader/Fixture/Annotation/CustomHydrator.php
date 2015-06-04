<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class CustomHydrator
 * 
 * @DM\CustomHydrator(
 *      class="CustomHydratorClassName",
 *      hydrateMethod="hydrateMethod",
 *      extractMethod="extractMethod"
 * )
 */
class CustomHydrator
{
}
