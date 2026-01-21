<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Custom collection class for addresses
 * This tests that the hydrator can use the native typehint to hydrate a custom collection class
 */
class AddressCollection
{
    private array $addresses = [];

    public function addAddress(Address $address): void
    {
        $this->addresses[] = $address;
    }

    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function setAddresses(array $addresses): self
    {
        $this->addresses = $addresses;
        return $this;
    }

    public function count(): int
    {
        return count($this->addresses);
    }
}
