<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="source",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkException",
 *          exceptionClass="\RuntimeException",
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
class DataSourceExceptionOnFail
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="source")
     */
    public $property;
}
