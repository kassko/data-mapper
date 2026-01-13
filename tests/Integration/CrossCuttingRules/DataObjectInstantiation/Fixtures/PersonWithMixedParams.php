<?php

declare(strict_types=1);

namespace Kassko\Sample\DataObjectInstantiation;

use Kassko\DataMapper\Attribute\Param;

/**
 * Person with mixed optional and #[Param] annotated constructor parameters
 */
class PersonWithMixedParams
{
    public ?string $socialSecurityNumber;
    public string $id;
    public bool $active;
    
    public function __construct(
        ?string $socialSecurityNumber = null,
        #[Param(value: "expr(context('userId'))")]
        string $id = '',
        bool $active = true
    ) {
        $this->socialSecurityNumber = $socialSecurityNumber;
        $this->id = $id;
        $this->active = $active;
    }
}
