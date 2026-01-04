<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Defines a method alias that can be referenced by DataSource, SinglePropDataSource, or MultiPropDataSource.
 * 
 * This allows defining reusable method references at class level that can be used via the methodAlias
 * parameter in data source attributes.
 * 
 * Usage:
 * ```php
 * #[MethodAlias(name: 'fetchPersonData', class: 'person.data_source', method: 'fetchData')]
 * class Person
 * {
 *     #[DataSource(methodAlias: 'fetchPersonData', args: ['#id'])]
 *     private ?string $email = null;
 * }
 * ```
 * 
 * The method also becomes available in expressions as `expr('fetchPersonData()')`.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class MethodAlias
{
    public function __construct(
        public readonly string $name,            // Alias name to reference this method
        public readonly string $class,           // Service class or service ID
        public readonly string $method,          // Method name to call
        public readonly bool $cascade = true,    // Whether this attribute cascades to child classes
        public readonly bool $enabled = true,    // Whether this attribute is active (disabled attributes are ignored)
    ) {}
}
