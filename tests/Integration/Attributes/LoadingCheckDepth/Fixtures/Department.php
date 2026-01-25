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

namespace Kassko\Sample\LoadingDepth;

use Kassko\DataMapper\Attribute\Property;

/**
 * Level 2 - Department (contains teams)
 */
class Department
{
    private ?string $name = null;
    private ?string $code = null;
    
    #[Property(class: Team::class)]
    private ?Team $mainTeam = null;

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

    public function getMainTeam(): ?Team
    {
        return $this->mainTeam;
    }

    public function setMainTeam(?Team $mainTeam): void
    {
        $this->mainTeam = $mainTeam;
    }
}
