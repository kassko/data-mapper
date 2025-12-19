<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class DataSource
{
    public const SCOPE_ALL = 'all';
    public const SCOPE_PROPERTY = 'property';
    public const SCOPE_ONLY_KEYS = 'data_source_only_keys';
    public const SCOPE_EXCEPT_KEYS = 'data_source_except_keys';

    /**
     * @param string|null $id Optional identifier for referencing from DataSourcesStore
     * @param string $class The DataSource class name or service identifier (prefixed with @)
     * @param string $method The method to call on the DataSource
     * @param array $args Arguments to pass to the method (can include property references like #propertyName)
     * @param bool $supplySeveralProperties Whether this DataSource returns an associative array for multiple properties (default: false)
     * @param string $loadingScope Scope control: 'all', 'property', 'data_source_only_keys', 'data_source_except_keys'
     * @param array $loadingScopeKeys Keys to include/exclude based on loadingScope
     */
    public function __construct(
        public readonly string $class,
        public readonly string $method,
        public readonly array $args = [],
        public readonly ?string $id = null,
        public readonly bool $supplySeveralProperties = false,
        public readonly string $loadingScope = self::SCOPE_ALL,
        public readonly array $loadingScopeKeys = [],
    ) {
    }
}
