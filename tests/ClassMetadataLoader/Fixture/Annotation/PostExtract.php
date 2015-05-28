<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * Class ProvidersStore
 * 
 * @DM\PostExtract(
 *      class="CustomHydratorClassName",
 *      method="postExtractMethodName"
 * )
 */
class PostExtract
{
}
