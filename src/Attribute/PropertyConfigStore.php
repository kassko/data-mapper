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
    public readonly array $configs;

    /**
     * @param PropertyConfig[] $configs Array of PropertyConfig instances
     */
    public function __construct(array $configs)
    {
        $indexed = [];
        foreach ($configs as $config) {
            if (!$config instanceof PropertyConfig) {
                throw new \InvalidArgumentException('PropertyConfigStore: all items must be PropertyConfig instances');
            }
            if (isset($indexed[$config->id])) {
                throw new \InvalidArgumentException(sprintf('PropertyConfigStore: duplicate config id "%s"', $config->id));
            }
            $indexed[$config->id] = $config;
        }
        $this->configs = $indexed;
    }
}
