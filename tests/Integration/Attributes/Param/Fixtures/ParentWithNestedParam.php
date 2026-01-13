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

namespace Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures;

use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'parentSource', class: 'parentSource', method: 'getData'),
])]
class ParentWithNestedParam
{
    use LoadableTrait;
    
    #[DataSourceRef(id: 'parentSource')]
    private ?string $title = null;
    
    #[Property(class: NestedPersonWithParam::class)]
    #[DataSourceRef(id: 'parentSource')]
    private ?NestedPersonWithParam $child = null;
    
    public function getTitle(): ?string
    {
        $this->loadProperty('title');
        return $this->title;
    }
    
    public function getChild(): ?NestedPersonWithParam
    {
        $this->loadProperty('child');
        return $this->child;
    }
}
