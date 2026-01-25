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
 * Company with depth=1 and noExpand filter on team loading.
 * 
 * With depth=1, Team and its direct Employee objects are hydrated.
 * With noExpand="members", the members property of Team should be skipped
 * (only manager and assistant will be hydrated).
 */
class CompanyWithDepth1AndNoExpand
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    #[SinglePropDataSource(class: TeamDataSource::class, method: 'getData')]
    #[Property(class: Team::class, noExpand: 'members')]
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
