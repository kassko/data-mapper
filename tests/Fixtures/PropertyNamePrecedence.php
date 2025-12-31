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

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Fixture for testing that Property.name takes precedence over PropertyConfig.name
 * 
 * The 'name' field controls how the CHILD object's properties are mapped from raw data.
 */
#[PropertyConfigStore([
    // PropertyConfig.name is 'config_value' - would map child's 'value' prop from 'config_value' in raw data
    new PropertyConfig(id: 'itemConfig', class: PropertyNameItem::class, name: 'config_value'),
])]
class PropertyNamePrecedence
{
    use LoadableTrait;

    private ?int $id = null;

    /**
     * Property.name 'property_value' takes precedence over PropertyConfig.name 'config_value'
     * The child object's 'value' property will be mapped from 'property_value' in raw data.
     */
    #[SinglePropDataSource(class: PropertyNameDataSource::class, method: 'getItems', args: ['#id'])]
    #[Property(config: 'itemConfig', name: 'property_value')]
    private array $items = [];

    /**
     * When Property.name is not set, PropertyConfig.name 'config_value' should be used.
     * The child object's 'value' property will be mapped from 'config_value' in raw data.
     */
    #[SinglePropDataSource(class: PropertyNameDataSource::class, method: 'getSingleItem', args: ['#id'])]
    #[Property(config: 'itemConfig')]
    private ?PropertyNameItem $singleItem = null;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getItems(): array
    {
        $this->loadProperty('items');
        return $this->items;
    }

    public function getSingleItem(): ?PropertyNameItem
    {
        $this->loadProperty('singleItem');
        return $this->singleItem;
    }
}
