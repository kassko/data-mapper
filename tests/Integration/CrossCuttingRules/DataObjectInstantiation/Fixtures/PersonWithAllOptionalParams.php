<?php

declare(strict_types=1);

namespace Kassko\Sample\DataObjectInstantiation;

/**
 * Person with all optional constructor parameters (no #[Param] required)
 */
class PersonWithAllOptionalParams
{
    public ?string $firstName;
    public ?string $lastName;
    public int $age;
    
    public function __construct(
        ?string $firstName = null,
        ?string $lastName = null,
        int $age = 0
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
    }
}
