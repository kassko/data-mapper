<?php

declare(strict_types=1);

namespace Kassko\Sample;

class ValidationService
{
    private array $validatedObjects = [];

    public function validatePerson(object $person): void
    {
        $this->validatedObjects[] = $person;
    }

    public function getValidatedObjects(): array
    {
        return $this->validatedObjects;
    }
}
