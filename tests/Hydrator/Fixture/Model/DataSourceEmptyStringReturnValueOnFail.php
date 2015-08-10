<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="source",
 *          class="EmptyStringSource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkReturnValue",
 *          badReturnValue="emptyString",
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
class DataSourceEmptyStringReturnValueOnFail
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="source")
     */
    public $property;
}
