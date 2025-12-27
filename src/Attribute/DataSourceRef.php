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

#[Attribute(Attribute::TARGET_PROPERTY)]
final class DataSourceRef
{
    public readonly ?string $id;
    public readonly ?array $fallbacks;
    public readonly ?array $providers;
    public readonly ?string $exceptionOnNoValidDataSource;
    public readonly int $priority;

    public function __construct(
        ?string $id = null,
        ?array $fallbacks = null,
        ?array $providers = null,
        ?string $exceptionOnNoValidDataSource = null,
        int $priority = 0
    ) {
        // Build-time validation: id and providers are mutually exclusive
        if ($id !== null && $providers !== null) {
            throw new \InvalidArgumentException('DataSourceRef: id and providers are mutually exclusive. Use either id (with optional fallbacks) or providers, not both.');
        }
        
        // Build-time validation: at least one must be set
        if ($id === null && $providers === null) {
            throw new \InvalidArgumentException('DataSourceRef requires either id or providers');
        }
        
        // Build-time validation: fallbacks only valid with id
        if ($fallbacks !== null && $id === null) {
            throw new \InvalidArgumentException('DataSourceRef: fallbacks can only be used with id');
        }
        
        // Build-time validation: exceptionOnNoValidDataSource requires fallbacks
        if ($exceptionOnNoValidDataSource !== null && $fallbacks === null) {
            throw new \InvalidArgumentException('DataSourceRef: exceptionOnNoValidDataSource requires fallbacks to be set');
        }
        
        $this->id = $id;
        $this->fallbacks = $fallbacks;
        $this->providers = $providers;
        $this->exceptionOnNoValidDataSource = $exceptionOnNoValidDataSource;
        $this->priority = $priority;
    }
}
