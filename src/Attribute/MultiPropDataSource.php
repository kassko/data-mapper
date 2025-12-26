<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Defines a data source that hydrates multiple properties.
 * The source returns an associative array where keys map to properties.
 * 
 * Can be used:
 * - Inside DataSourcesStore (with id) for referencing via DataSourceRef
 * - Directly on properties (standalone, cannot be combined with DataSourceRef)
 * 
 * Scope: ONLY on properties (not on classes)
 * Cannot be combined with: DataSource, SinglePropDataSource, DataSourceRef
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class MultiPropDataSource
{
    public const SCOPE_ALL = 'all';
    public const SCOPE_ONLY_KEYS = 'only_keys';
    public const SCOPE_EXCEPT_KEYS = 'except_keys';
    public const SCOPE_ONLY_PROPS = 'only_props';
    public const SCOPE_EXCEPT_PROPS = 'except_props';

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $class = null,
        public readonly string $method = '',
        public readonly array $args = [],
        public readonly string $loadingScope = self::SCOPE_ALL,
        public readonly array $loadingScopeKeys = [],    // Filter by raw data keys
        public readonly array $loadingScopeProps = [],   // Filter by property names (NEW)
        public readonly int $priority = 0,
    ) {}
}
