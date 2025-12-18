<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class DataSourceRef
{
    public readonly string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
