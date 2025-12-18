<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataSource
{
    /**
     * @param string $class The DataSource class name or service identifier (prefixed with @)
     * @param string $method The method to call on the DataSource
     * @param array $args Arguments to pass to the method (can include property references like #propertyName)
     */
    public function __construct(
        public readonly string $class,
        public readonly string $method,
        public readonly array $args = []
    ) {
    }
}
