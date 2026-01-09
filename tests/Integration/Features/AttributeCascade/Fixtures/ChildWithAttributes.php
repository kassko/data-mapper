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
 * Child class that shadows parent's private property with its own.
 * The child's attributes should take precedence (use 'child_name' instead of 'parent_name').
 */
class ChildWithAttributes extends ParentWithAttributes
{
    #[Property(sourceField: 'child_name')]
    private ?string $name = null;
    
    public function getName(): ?string
    {
        return $this->name;
    }
}
