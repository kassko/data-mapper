<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Context
{
    public function __construct(
        public readonly array $values = [],  // Key-value pairs
    ) {}
}
