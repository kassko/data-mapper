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

namespace Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures;

/**
 * Person DTO with 3-level deep nested address.
 */
class PersonWithDeepAddressDto
{
    public function __construct(
        public string $firstName = '',
        public string $lastName = '',
        public ?AddressWithStreetDto $address = null
    ) {
    }
}
