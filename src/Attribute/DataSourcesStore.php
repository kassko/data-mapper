<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DataSourcesStore
{
    /**
     * @param array<SinglePropDataSource|DataSource> $sources
     */
    public function __construct(
        public readonly array $sources = [],
    ) {}
}
