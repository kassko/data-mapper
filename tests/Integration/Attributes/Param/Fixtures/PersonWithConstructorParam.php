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

class PersonWithConstructorParam
{
    public string $name;
    
    public function __construct(
        #[Param(value: "expr(context('defaultName'))")]
        string $name
    ) {
        $this->name = $name;
    }
}
