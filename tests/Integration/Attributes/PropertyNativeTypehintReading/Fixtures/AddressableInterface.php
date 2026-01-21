<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Attributes\PropertyNativeTypehintReading\Fixtures;

/**
 * Interface for addressable entities
 */
interface AddressableInterface
{
    public function getStreet(): ?string;
    public function getCity(): ?string;
}
