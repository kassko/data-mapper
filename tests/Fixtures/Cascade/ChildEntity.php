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

use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Child class that inherits DataSources from parent and trait.
 * Also defines its own DataSources, including one that overrides parent's sharedSource.
 */
#[DataSourcesStore([
    new SinglePropDataSource(id: 'childSource', class: ChildDataSource::class, method: 'getData'),
    new SinglePropDataSource(id: 'sharedSource', class: ChildDataSource::class, method: 'getShared'), // Overrides parent
])]
class ChildEntity extends BaseEntity
{
    use LoadableTrait;
    use DataSourceTrait;
    
    private ?string $childValue = null;
    private ?string $sharedValue = null;
    
    #[DataSourceRef(id: 'childSource')]
    private ?string $fromChildSource = null;
    
    #[DataSourceRef(id: 'parentSource')] // Reference parent's source
    private ?string $fromParentSource = null;
    
    #[DataSourceRef(id: 'traitSource')] // Reference trait's source
    private ?string $fromTraitSource = null;
    
    #[DataSourceRef(id: 'sharedSource')] // Should use child's override
    private ?string $fromSharedSource = null;
    
    public function getChildValue(): ?string
    {
        return $this->childValue;
    }
    
    public function getSharedValue(): ?string
    {
        return $this->sharedValue;
    }
    
    public function getFromChildSource(): ?string
    {
        $this->loadProperty('fromChildSource');
        return $this->fromChildSource;
    }
    
    public function getFromParentSource(): ?string
    {
        $this->loadProperty('fromParentSource');
        return $this->fromParentSource;
    }
    
    public function getFromTraitSource(): ?string
    {
        $this->loadProperty('fromTraitSource');
        return $this->fromTraitSource;
    }
    
    public function getFromSharedSource(): ?string
    {
        $this->loadProperty('fromSharedSource');
        return $this->fromSharedSource;
    }
}
