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

namespace Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures;

/**
 * Simple Address class for testing
 */
class Address
{
    public ?string $street = null;
    public ?string $city = null;
    
    public function __construct(?string $street = null, ?string $city = null)
    {
        $this->street = $street;
        $this->city = $city;
    }
}
