<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="sourceNull",
 *          class="NullSource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkReturnValue",
 *          badReturnValue="null",
 *          fallbackSourceId="sourceFallback"
 *      ),
 *      @DM\DataSource(
 *          id="sourceFalse",
 *          class="FalseSource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkReturnValue",
 *          badReturnValue="false",
 *          fallbackSourceId="sourceFallback"
 *      ),
 *      @DM\DataSource(
 *          id="sourceEmptyString",
 *          class="EmptyStringSource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkReturnValue",
 *          badReturnValue="emptyString",
 *          fallbackSourceId="sourceFallback"
 *      ),
 *      @DM\DataSource(
 *          id="sourceEmptyArray",
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
class DataSourceBadReturnValueOnFail
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="sourceNull")
     */
    public $propertyNull;
    /**
     * @DM\RefSource(id="sourceFalse")
     */
    public $propertyFalse;
    /**
     * @DM\RefSource(id="sourceEmptyString")
     */
    public $propertyEmptyString;
    /**
     * @DM\RefSource(id="sourceEmptyArray")
     */
    public $propertyEmptyArray;
}
