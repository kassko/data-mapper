<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="someSource",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SomeDataSource",
 *          method="getData",
 *          lazyLoading=false,
 *          onFail="checkException",
 *          exceptionClass="\RuntimeException"
 *      )
 * })
 */
class DataSourceExceptionOnFail
{

}
