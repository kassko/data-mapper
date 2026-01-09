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

namespace Kassko\DataMapper\DataCollector;

/**
 * Represents a single cascade event during attribute inheritance.
 */
final class CascadeEvent
{
    public const TYPE_DATASOURCES_STORE_MERGE = 'datasources_store_merge';
    public const TYPE_PROPERTY_CONFIG_STORE_MERGE = 'property_config_store_merge';
    public const TYPE_DATASOURCE_ID_CONFLICT = 'datasource_id_conflict';
    public const TYPE_PROPERTY_CONFIG_ID_CONFLICT = 'property_config_id_conflict';
    public const TYPE_PROPERTY_ATTRIBUTE_OVERRIDE = 'property_attribute_override';
    public const TYPE_WHEN_EXPRESSION_TYPE_COERCION = 'when_expression_type_coercion';
    public const TYPE_SKIPPED_DATASOURCE_PROPERTY = 'skipped_datasource_property';

    public readonly float $timestamp;

    public function __construct(
        public readonly string $type,
        public readonly string $targetClass,
        public readonly string $sourceClass,
        public readonly string $sourceType,
        public readonly array $metadata = [],
        ?float $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? microtime(true);
    }
}
