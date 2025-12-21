<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Getter
{
    public const TYPE_GETTER = 'getter';
    public const TYPE_ISSER = 'isser';
    public const TYPE_HASER = 'haser';

    public function __construct(
        public readonly ?string $name = null,  // Method name
        public readonly string $type = self::TYPE_GETTER,  // 'getter', 'isser', 'haser'
    ) {}
}
