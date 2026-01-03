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

namespace Kassko\Sample\CustomHydrator;

use Kassko\DataMapper\Attribute\CustomHydrator as CustomHydratorAttr;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Fixture for testing CustomHydrator functionality.
 */
class PersonCustomHydrator
{
    use LoadableTrait;

    private int $id;

    #[CustomHydratorAttr(key: 'address_hydrator', objectClass: Address::class)]
    private ?Address $address = null;

    #[CustomHydratorAttr(key: 'phone_hydrator')]
    private ?string $phone = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAddress(): ?Address
    {
        $this->loadProperty('address');
        return $this->address;
    }

    public function getPhone(): ?string
    {
        $this->loadProperty('phone');
        return $this->phone;
    }
}
