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

namespace Kassko\Sample\Cascade;

use Kassko\DataMapper\Attribute\Property;

/**
 * Parent class with a private property that has attributes.
 */
class ParentWithAttributes
{
    #[Property(key: 'parent_name')]
    private ?string $name = null;
    
    private ?int $id = null;
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
}
