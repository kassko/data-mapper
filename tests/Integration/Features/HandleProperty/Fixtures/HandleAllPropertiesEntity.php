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
 * Entity with HandleAllProperties(value: true) - all properties are hydrated by default.
 */
#[HandleAllProperties(value: true)]
class HandleAllPropertiesEntity
{
    #[Property(key: 'name')]
    private string $name = '';

    #[Property(key: 'email')]
    private string $email = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
