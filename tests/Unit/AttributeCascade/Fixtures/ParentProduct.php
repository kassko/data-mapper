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

/**
 * Product class used in parent config.
 */
class ParentProduct
{
    private ?string $name = null;
    private ?string $origin = 'parent';
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function getOrigin(): ?string
    {
        return $this->origin;
    }
}
