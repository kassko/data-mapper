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

namespace Kassko\Sample\Hydrator;

use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertySettingHook;

/**
 * Fixture for testing hydration with hooks.
 */
#[PropertyInstantiatingHook(after_instantiating: 'onInitialized', args: ['##object'])]
class HydratableWithHooks
{
    private bool $initialized = false;
    private bool $beforeSetNameCalled = false;
    private bool $afterSetNameCalled = false;

    #[Property(sourceField: 'first_name')]
    #[PropertySettingHook(before_set_property: 'beforeSetName', args: ["expr(rawDataItem('first_name'))"])]
    #[PropertySettingHook(after_set_property: 'afterSetName', args: ['##object', '#firstName'])]
    private ?string $firstName = null;

    private ?string $lastName = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function onInitialized(self $obj): void
    {
        $this->initialized = true;
    }

    public function beforeSetName(?string $name): void
    {
        $this->beforeSetNameCalled = true;
    }

    public function afterSetName(self $obj, ?string $firstName): void
    {
        $this->afterSetNameCalled = true;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function isBeforeSetNameCalled(): bool
    {
        return $this->beforeSetNameCalled;
    }

    public function isAfterSetNameCalled(): bool
    {
        return $this->afterSetNameCalled;
    }
}
