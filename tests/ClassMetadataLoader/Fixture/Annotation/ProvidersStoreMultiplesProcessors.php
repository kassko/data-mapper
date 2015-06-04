<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\ProvidersStore({
 *      @DM\Provider(
 *          id="personSource",
 *
 *          preprocessors = @DM\Methods({
 *              @DM\Method(method="somePrepocessorA"),
 *              @DM\Method(method="somePrepocessorB")
 *          }),
 *          processors = @DM\Methods({ 
 *              @DM\Method(method="someProcessorA"),
 *              @DM\Method(method="someProcessorB")
 *          }),
 *      )
 * })
 */
class ProvidersStoreMultiplesProcessors
{

}
