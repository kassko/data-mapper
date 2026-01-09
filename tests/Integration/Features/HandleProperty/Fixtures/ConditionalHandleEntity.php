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

use Kassko\DataMapper\Attribute\HandleProperty;
use Kassko\DataMapper\Attribute\Property;

/**
 * Entity with conditional HandleProperty using 'when' expressions.
 * 
 * Logic:
 * - HandleProperty(value: true, when: condition) = hydrate if condition is true, skip if false
 * - HandleProperty(value: false, when: condition) = skip if condition is true, hydrate if false
 */
class ConditionalHandleEntity
{
    #[Property(sourceField: 'name')]
    private string $name = '';

    // Include email only when 'include_email' context key exists
    // value=true, when=true -> hydrate
    // value=true, when=false -> skip (SAUF)
    #[Property(sourceField: 'email')]
    #[HandleProperty(value: true, when: "expr(contextKeyExists('include_email'))")]
    private string $email = '';

    // Skip phone only when 'hide_phone' context key exists  
    // value=false, when=true -> skip
    // value=false, when=false -> hydrate (SAUF)
    #[Property(sourceField: 'phone')]
    #[HandleProperty(value: false, when: "expr(contextKeyExists('hide_phone'))")]
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
