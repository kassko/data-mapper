<?php

declare(strict_types=1);

namespace Kassko\Sample\DataObjectInstantiation;

use Kassko\DataMapper\Attribute\Param;

/**
 * Person with only #[Param] annotated constructor parameters
 */
class PersonWithOnlyParamAttributes
{
    public string $firstName;
    public string $lastName;
    
    public function __construct(
        #[Param(value: "expr(context('defaultFirstName'))")]
        string $firstName,
        #[Param(value: "expr(context('defaultLastName'))")]
        string $lastName
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
