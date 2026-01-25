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
 * Organization with no depth limit on department loading.
 * 
 * Without a depth parameter (or with depth=null), all nested objects
 * will be fully hydrated recursively.
 */
class OrganizationWithNoLimit
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    #[SinglePropDataSource(class: DepartmentDataSource::class, method: 'getData')]
    #[Property(class: Department::class)]
    #[Loading]
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
