<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Defines a data source that hydrates a single property.
 * The source returns a value (object, scalar, or non-associative array) for the property.
 * 
 * Scope: ONLY on properties (not on classes)
 * Cannot be combined with: DataSource, MultiPropDataSource, DataSourceRef
 * Can be used in DataSourcesStore with an id for referencing via DataSourceRef
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class SinglePropDataSource
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $class = null,
        public readonly string $method = '',
        public readonly array $args = [],
        public readonly int $priority = 0,
        // NO loadingScope/loadingScopeKeys - single property only
    ) {}
}
