<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 * 
 * @DM\PreExtract(
 *      class="CustomHydratorClassName",
 *      method="methodName"
 * )
 */
class PreExtract
{
}
