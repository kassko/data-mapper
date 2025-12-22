<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\Hook;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[Hook(name: 'after_create_object', method: 'initializeObject', args: ['##object'])]
class PersonWithHooks
{
    use LoadableTrait;

    private ?int $id = null;
    private bool $initialized = false;
    private ?string $validatedFirstName = null;
    private bool $nameSetCalled = false;

    #[Hook(name: 'before_set_property', method: 'validateName', args: ["expr(rawDataItem('first_name'))"])]
    #[Hook(name: 'after_set_property', method: 'onNameSet', args: ['##object', '#firstName'])]
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
