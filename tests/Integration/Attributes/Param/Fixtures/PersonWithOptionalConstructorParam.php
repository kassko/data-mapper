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

namespace Kassko\DataMapper\Tests\Integration\Attributes\Param\Fixtures;

use Kassko\DataMapper\Attribute\Param;

/**
 * Person with optional constructor parameter (no #[Param] required)
 */
class PersonWithOptionalConstructorParam
{
    public ?string $socialSecurityNumber;
    public string $id;
    
    public function __construct(
        ?string $socialSecurityNumber = null,
        #[Param(value: "expr(context('userId'))")]
        string $id = ''
    ) {
        $this->socialSecurityNumber = $socialSecurityNumber;
        $this->id = $id;
    }
}
