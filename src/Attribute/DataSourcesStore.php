<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DataSourcesStore
{
    /** @var DataSource[] */
    public readonly array $sources;

    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
    }
}
