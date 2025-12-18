<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field
{
    public readonly ?string $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}
