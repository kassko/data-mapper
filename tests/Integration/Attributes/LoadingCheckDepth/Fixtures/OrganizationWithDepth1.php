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

use Kassko\DataMapper\Attribute\Loading;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Organization with depth=1 on department loading.
 * 
 * With depth=1, the Department object and its direct children (mainTeam)
 * will be hydrated, but Team's nested properties (leader, members) will NOT.
 */
class OrganizationWithDepth1
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    #[SinglePropDataSource(class: DepartmentDataSource::class, method: 'getData')]
    #[Property(class: Department::class)]
    #[Loading(depth: 1)]
    private ?Department $department = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDepartment(): ?Department
    {
        $this->loadProperty('department');
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }
}
