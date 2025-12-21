<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Setter
{
    public const TYPE_SETTER = 'setter';
    public const TYPE_ADDER = 'adder';

    public function __construct(
        public readonly ?string $name = null,  // Method name
        public readonly string $type = self::TYPE_SETTER,  // 'setter' or 'adder'
    ) {}
}
