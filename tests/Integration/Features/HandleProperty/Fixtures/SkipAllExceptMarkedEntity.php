<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Sample\HandleProperty;

use Kassko\DataMapper\Attribute\HandleAllProperties;
use Kassko\DataMapper\Attribute\HandleProperty;
use Kassko\DataMapper\Attribute\Property;

/**
 * Entity with HandleAllProperties(value: false) - only explicitly marked properties are hydrated.
 * Only 'name' is marked with HandleProperty(value: true), so only 'name' should be hydrated.
 */
#[HandleAllProperties(value: false)]
class SkipAllExceptMarkedEntity
{
    #[Property(sourceField: 'name')]
    #[HandleProperty(value: true)]
    private string $name = '';

    #[Property(sourceField: 'email')]
    private string $email = '';

    #[Property(sourceField: 'phone')]
    private string $phone = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
