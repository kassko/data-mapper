<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Loading
{
    public const TYPE_LAZY = 'lazy';
    public const TYPE_EAGER = 'eager';

    public function __construct(
        public readonly string $type = self::TYPE_LAZY,  // 'lazy' or 'eager'
        public readonly ?int $depth = null,              // Max recursion depth
    ) {}
}
