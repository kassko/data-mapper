<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource",
 *			depends={"#dependsFirst", "#dependsSecond", "#dependsThird"},
 *      )
 * })
 */
class DataSourcesStoreMultiplesDepends
{

}
