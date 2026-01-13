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

use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\Attribute\Setter;

/**
 * Person with indexed adder that uses Param for additional parameters
 */
class PersonWithIndexedAdder
{
    #[Setter(name: 'addAddress', type: Setter::TYPE_INDEXED_ADDER)]
    private array $addresses = [];
    
    private array $methodCalls = [];

    public function addAddress(
        mixed $index,
        mixed $address,
        #[Param(value: "expr(context('address_prefix'))")]
        string $addressPrefix = ''
    ): void {
        $key = $addressPrefix . $index;
        $this->addresses[$key] = $address;
        $this->methodCalls[] = ['index' => $index, 'prefix' => $addressPrefix, 'key' => $key];
    }

    public function getAddresses(): array
    {
        return $this->addresses;
    }
    
    public function getMethodCalls(): array
    {
        return $this->methodCalls;
    }
}
