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

use Kassko\DataMapper\Attribute\Property;

/**
 * Team model (Level 1 - contains employees)
 */
class Team
{
    private ?string $name = null;
    private ?string $code = null;
    
    #[Property(class: Employee::class)]
    private ?Employee $manager = null;
    
    #[Property(class: Employee::class)]
    private ?Employee $assistant = null;
    
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getManager(): ?Employee
    {
        return $this->manager;
    }

    public function setManager(?Employee $manager): void
    {
        $this->manager = $manager;
    }

    public function getAssistant(): ?Employee
    {
        return $this->assistant;
    }

    public function setAssistant(?Employee $assistant): void
    {
        $this->assistant = $assistant;
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
