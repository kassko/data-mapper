<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataSource
{
    /**
     * @param string|null $id Optional identifier for referencing from DataSourcesStore
     * @param string $class The DataSource class name or service identifier (prefixed with @)
     * @param string $method The method to call on the DataSource
     * @param array $args Arguments to pass to the method (can include property references like #propertyName)
     * @param bool $supplySeveralFields Whether this DataSource returns an associative array for multiple fields (default: false)
     */
    public function __construct(
        public readonly string $class,
        public readonly string $method,
        public readonly array $args = [],
        public readonly ?string $id = null,
        public readonly bool $supplySeveralFields = false
    ) {
    }
}
