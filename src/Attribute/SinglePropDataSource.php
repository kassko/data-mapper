<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Defines a data source that hydrates a single property.
 * The source returns a value (object, scalar, or non-associative array) for the property.
 * 
 * This attribute can be placed on the PROPERTY level or CLASS level (when referenced by DataSourceRef).
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class SinglePropDataSource
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $class = null,
        public readonly string $method = '',
        public readonly array $args = [],
        // NO loadingScope/loadingScopeKeys - single property only
    ) {}
}
