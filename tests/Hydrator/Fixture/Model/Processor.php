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
 *          preprocessor = @DM\Method(method="somePreprocessor"),
 *          processor = @DM\Method(method="someProcessor")
 *      )
 * })
 */
class Processor
{
    use ProcessorTrait;
}
