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

namespace Kassko\Sample\SinglePropDataSource;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Basic product entity using SinglePropDataSource for lazy loading.
 */
class Product
{
    use LoadableTrait;

    private int $id;

    #[SinglePropDataSource(class: ProductDataSource::class, method: 'getName', args: ['#id'])]
    private ?string $name = null;

    #[SinglePropDataSource(class: ProductDataSource::class, method: 'getPrice', args: ['#id'])]
    private ?float $price = null;

    #[SinglePropDataSource(class: ProductDataSource::class, method: 'getDescription', args: ['#id'])]
    private ?string $description = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getPrice(): ?float
    {
        $this->loadProperty('price');
        return $this->price;
    }

    public function getDescription(): ?string
    {
        $this->loadProperty('description');
        return $this->description;
    }
}
