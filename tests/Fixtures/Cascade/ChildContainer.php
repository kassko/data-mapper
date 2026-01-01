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

namespace Kassko\Sample\Cascade;

use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;

/**
 * Child class that inherits PropertyConfigs from parent and trait.
 * Also defines its own configs, including one that overrides parent's sharedConfig.
 */
#[PropertyConfigStore([
    new PropertyConfig(id: 'childConfig', class: ChildProduct::class),
    new PropertyConfig(id: 'sharedConfig', class: ChildProduct::class), // Overrides parent
])]
class ChildContainer extends BaseContainer
{
    use PropertyConfigTrait;
    
    #[Property(
        configCandidates: [
            ['id' => 'childConfig', 'rule' => "expr(rawDataItemExists('childType'))"],
            ['id' => 'parentConfig', 'rule' => "expr(rawDataItemExists('parentType'))"],
            ['id' => 'traitConfig', 'rule' => "expr(rawDataItemExists('traitType'))"],
        ],
        defaultConfigCandidate: 'sharedConfig'
    )]
    private ?object $item = null;
    
    public function getItem(): ?object
    {
        return $this->item;
    }
}
