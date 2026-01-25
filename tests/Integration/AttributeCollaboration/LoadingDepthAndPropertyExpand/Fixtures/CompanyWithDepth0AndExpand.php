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

namespace Kassko\Sample\LoadingDepthExpand;

use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Company with depth=0 and expand filter on team loading.
 * 
 * With depth=0, only Team itself is hydrated (scalars only).
 * Even with expand="manager", the manager should NOT be hydrated
 * because depth=0 prevents any nested object hydration.
 * 
 * This tests that depth takes precedence over expand.
 */
class CompanyWithDepth0AndExpand
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    #[SinglePropDataSource(class: TeamDataSource::class, method: 'getData')]
    #[Property(class: Team::class, expand: 'name,code,manager')]
    #[Loading(depth: 0)]
    private ?Team $team = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
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
