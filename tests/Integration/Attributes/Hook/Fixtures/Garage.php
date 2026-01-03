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

namespace Kassko\Sample\Hook;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyConfig;
use Kassko\DataMapper\Attribute\PropertyConfigStore;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[PropertyInstantiatingHook(after_instantiating: 'onCreated', args: ['##object'])]
#[PropertyConfigStore([
    new PropertyConfig(id: 'gasolineCar', class: GasolineCar::class),
    new PropertyConfig(id: 'electricCar', class: ElectricCar::class),
])]
class Garage
{
    use LoadableTrait;

    private ?int $id = null;
    private bool $created = false;
    private bool $carsLoaded = false;

    #[SinglePropDataSource(class: GarageDataSource::class, method: 'getCars', args: ['#id'])]
    #[Property(
        configCandidates: [
            ['id' => 'gasolineCar', 'when' => "expr(rawDataItemExists('gasolineKind'))"],
            ['id' => 'electricCar', 'when' => "expr(rawDataItemExists('energyProvider'))"],
        ],
        defaultConfigCandidate: 'gasolineCar'
    )]
    #[PropertySettingHook(after_set_property: 'onCarsLoaded', args: ['##object', '#cars'])]
    private array $cars = [];

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function onCreated(self $garage): void
    {
        $this->created = true;
    }

    public function onCarsLoaded(self $garage, array $cars): void
    {
        $this->carsLoaded = true;
    }

    public function getCars(): array
    {
        $this->loadProperty('cars');
        return $this->cars;
    }

    public function isCreated(): bool
    {
        return $this->created;
    }

    public function isCarsLoaded(): bool
    {
        return $this->carsLoaded;
    }
}
