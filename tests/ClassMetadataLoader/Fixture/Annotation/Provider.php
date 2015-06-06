<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Fixture\Annotation;

use Kassko\DataMapper\Annotation as DM;

class Provider
{
    /**
     * @DM\Provider(
     *      id="providerFieldId",
     *      lazyLoading=true,
     *      supplySeveralFields=true,
     *      depends={"depend#1","depend#2"},
     *      onFail="checkException",
     *      exceptionClass="\RuntimeException",
     *      badReturnValue="emptyString",
     *      fallbackSourceId="firstFieldFallbackSourceId",
     *      preprocessor=@DM\Method(method="fooPreprocessor"),
     *      processor=@DM\Method(method="barProcessor"),
     *      class="\stdClass",
     *      method="someMethod",
     *      args={"argument#1", "argument#2"}
     * )
     */
    protected $providerField;
}