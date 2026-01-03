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

namespace Kassko\Sample\LoaderValidation;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Fixture to test that validation catches non-existent methods.
 */
class EntityWithNonExistentMethod
{
    use LoadableTrait;

    private int $id;

    #[DataSource(class: PersonDataSource::class, method: 'nonExistentMethod', args: ['#id'])]
    private ?string $data = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getData(): ?string
    {
        $this->loadProperty('data');
        return $this->data;
    }
}
