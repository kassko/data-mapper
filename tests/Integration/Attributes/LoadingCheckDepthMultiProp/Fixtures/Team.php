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

use Kassko\DataMapper\Attribute\Property;

/**
 * Team model (contains employees)
 */
class Team
{
    private ?string $name = null;
    
    #[Property(class: Employee::class)]
    private ?Employee $leader = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getLeader(): ?Employee
    {
        return $this->leader;
    }

    public function setLeader(?Employee $leader): void
    {
        $this->leader = $leader;
    }
}
