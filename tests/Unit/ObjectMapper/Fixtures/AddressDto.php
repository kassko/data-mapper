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

namespace Kassko\Sample\ObjectMapper;

/**
 * Nested address DTO.
 */
class AddressDto
{
    public function __construct(
        public string $street = '',
        public string $city = '',
        public string $postalCode = '',
        public ?string $country = null
    ) {
    }
}
