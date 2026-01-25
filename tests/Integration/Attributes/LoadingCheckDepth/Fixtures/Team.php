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
 * Level 3 - Team (contains employees)
 */
class Team
{
    private ?string $name = null;
    
    #[Property(class: Employee::class)]
    private ?Employee $leader = null;
    
    /** @var Employee[] */
    #[Property(class: Employee::class, itemClass: Employee::class)]
    private array $members = [];

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

    /**
     * @return Employee[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @param Employee[] $members
     */
    public function setMembers(array $members): void
    {
        $this->members = $members;
    }
}
