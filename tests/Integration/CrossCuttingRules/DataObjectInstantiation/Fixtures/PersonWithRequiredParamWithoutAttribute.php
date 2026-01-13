<?php

declare(strict_types=1);

namespace Kassko\Sample\DataObjectInstantiation;

/**
 * Person with a required constructor parameter without #[Param] (INVALID)
 */
class PersonWithRequiredParamWithoutAttribute
{
    public string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
