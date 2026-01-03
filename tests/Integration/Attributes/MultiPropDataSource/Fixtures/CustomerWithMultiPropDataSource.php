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

namespace Kassko\Sample\MultiPropDataSource;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class CustomerWithMultiPropDataSource 
{
    use LoadableTrait;

    #[MultiPropDataSource(class: CustomerMultiPropDataSource::class, method: 'fetchMultipleData')]
    private ?string $name = null;

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}
