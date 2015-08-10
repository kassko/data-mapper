<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="dataSource",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource",
 *          method="getData",
 *          lazyLoading = false,
 *          preprocessors = @DM\Methods({
 *              @DM\Method(method="somePreprocessorA"),
 *              @DM\Method(method="somePreprocessorB"),
 *              @DM\Method(class="Kassko\DataMapperTest\Hydrator\Fixture\Processor\SomeProcessor", method="process")
 *          }),
 *          processors = @DM\Methods({
 *              @DM\Method(method="someProcessorA"),
 *              @DM\Method(method="someProcessorB"),
 *              @DM\Method(class="Kassko\DataMapperTest\Hydrator\Fixture\Processor\SomeProcessor", method="process")
 *          })
 *      )
 * })
 */
class Processors
{
    use ProcessorTrait;
}
