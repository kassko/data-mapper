<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 * @package Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation
 * @DM\PostExtract(
 *      class="CustomHydratorClassName",
 *      method="postExtractMethodName"
 * )
 */
class PostExtract
{
}