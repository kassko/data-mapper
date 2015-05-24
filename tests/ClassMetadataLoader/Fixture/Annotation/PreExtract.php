<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 * @package Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation
 * @DM\PreExtract(
 *      class="CustomHydratorClassName",
 *      method="methodName"
 * )
 */
class PreExtract
{
}