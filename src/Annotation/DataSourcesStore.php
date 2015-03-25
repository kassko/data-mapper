<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class DataSourcesStore
{
    /**
     * One or more DataSource annotations.
     *
     * @var array<\Kassko\DataMapper\Annotation\DataSource>
     */
    public $items = [];
}
