<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="source",
 *          class="EmptyArraySource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkReturnValue",
 *          badReturnValue="emptyArray",
 *          fallbackSourceId="sourceFallback"
 *      ),
 *      @DM\DataSource(
 *          id="sourceFallback",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\FallbackDataSource",
 *          method="getData",
 *          lazyLoading=false
 *      )
 * })
 */
class DataSourceEmptyArrayReturnValueOnFail
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="source")
     */
    public $property;
}
