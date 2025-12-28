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

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[PropertyInstantiatingHook(after_instantiating: 'initializeObject', args: ['##object'])]
class PersonWithHooks
{
    use LoadableTrait;

    private ?int $id = null;
    private bool $initialized = false;
    private ?string $validatedFirstName = null;
    private bool $nameSetCalled = false;

    #[PropertySettingHook(before_set_property: 'validateName', args: ["expr(rawDataItem('first_name'))"])]
    #[PropertySettingHook(after_set_property: 'onNameSet', args: ['##object', '#firstName'])]
    private ?string $firstName = null;

    private ?string $lastName = null;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function initializeObject(self $obj): void
    {
        $this->initialized = true;
    }

    public function validateName(?string $name): void
    {
        // Store the validated name for testing
        $this->validatedFirstName = $name;
    }

    public function onNameSet(self $obj, ?string $firstName): void
    {
        $this->nameSetCalled = true;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function getValidatedFirstName(): ?string
    {
        return $this->validatedFirstName;
    }

    public function isNameSetCalled(): bool
    {
        return $this->nameSetCalled;
    }
}
