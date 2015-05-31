<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="personSource",
 *          class="Kassko\Sample\PersonDataSource",
 *          method="getData",
 *          args="#id",
 *          lazyLoading=true,
 *          supplySeveralFields=true,
 *          onFail="checkException",
 *          exceptionClass="\RuntimeException",
 *          badReturnValue="emptyString",
 *          fallbackSourceId="testFallbackSourceId",
 *          depends="#dependsFirst",
 *
 *          preprocessor = @DM\Method(method="somePreprocessor"),
 *          processor = @DM\Method(method="someProcessor"),
 *      )
 * })
 */
class DataSourcesStore
{

}
