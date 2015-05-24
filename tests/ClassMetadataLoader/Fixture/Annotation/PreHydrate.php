<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 * @package Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation
 * @DM\PreHydrate(
 *      class="CustomHydratorClassName",
 *      method="preHydrateMethodName"
 * )
 */
class PreHydrate
{
}