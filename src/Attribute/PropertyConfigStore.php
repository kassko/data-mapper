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
 * Stores reusable PropertyConfig definitions at class level.
 * PropertyConfigs can be referenced by ID from Property attributes.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class PropertyConfigStore
{
    /** @var array<string, PropertyConfig> Indexed by config ID */
    public readonly array $items;
    public readonly bool $cascade;  // Whether this store cascades to child classes
    public readonly bool $enabled;  // Whether this attribute is active

    /**
     * @param PropertyConfig[] $items Array of PropertyConfig instances
     * @param bool $cascade Whether this store cascades to child classes
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(array $items = [], bool $cascade = true, bool $enabled = true)
    {
        $indexed = [];
        foreach ($items as $config) {
            if (!$config instanceof PropertyConfig) {
                throw new \InvalidArgumentException('PropertyConfigStore: all items must be PropertyConfig instances');
            }
            if (isset($indexed[$config->id])) {
                throw new \InvalidArgumentException(sprintf('PropertyConfigStore: duplicate config id "%s"', $config->id));
            }
            $indexed[$config->id] = $config;
        }
        $this->items = $indexed;
        $this->cascade = $cascade;
        $this->enabled = $enabled;
    }
}
