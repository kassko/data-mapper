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
 * Adds key-value pairs to the hydration context.
 * 
 * Context values accumulate as hydration descends into nested objects.
 * Later values for the same key override earlier ones (last writer wins).
 * Context values are set AFTER the property is hydrated but BEFORE 
 * any PropertyCandidate discriminators are evaluated on child objects.
 * 
 * Usage:
 * ```php
 * #[Context(
 *     key1: 'value1',
 *     key2: 'value2',
 * )]
 * private Chief $chief;
 * ```
 * 
 * Access in expressions:
 * - `context('key1')` - returns the value or null if not found (logs warning)
 * - `contextKeyExists('key1')` - returns true/false
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Context
{
    /** @var array<string, mixed> Key-value pairs to add to context */
    public readonly array $values;

    /**
     * @param mixed ...$values Named arguments become key-value pairs
     */
    public function __construct(mixed ...$values)
    {
        $this->values = $values;
    }
}
