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

namespace Kassko\Sample\LoadingDepthMultiProp;

use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Company with MultiPropDataSource and depth=1.
 * 
 * With depth=1, Team and its direct children (Employee leader)
 * should be hydrated.
 */
#[DataSourcesStore([
    new MultiPropDataSource(id: 'companyData', class: CompanyDataSource::class, method: 'getData'),
])]
class CompanyWithMultiPropDepth1
{
    use LoadableTrait;
    
    // Cross-hydrated automatically from 'name' key in the returned data
    private ?string $name = null;
    
    // Cross-hydrated automatically from 'code' key
    private ?string $code = null;
    
    // This property triggers the MultiPropDataSource and has depth=1
    #[DataSourceRef(id: 'companyData')]
    #[Property(class: Team::class)]
    #[Loading(depth: 1)]
    private ?Team $team = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getTeam(): ?Team
    {
        $this->loadProperty('team');
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
