<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyCandidates;
use Kassko\DataMapper\Attribute\PropertyCandidate;
use Kassko\DataMapper\Attribute\Hook;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[Hook(name: 'after_create_object', method: 'onCreated', args: ['##this'])]
class Garage
{
    use LoadableTrait;

    private ?int $id = null;
    private bool $created = false;
    private bool $carsLoaded = false;

    #[SinglePropDataSource(class: GarageDataSource::class, method: 'getCars', args: ['#id'])]
    #[PropertyCandidates([
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('gasolineKind'))",
            property: new Property(class: GasolineCar::class)
        ),
        new PropertyCandidate(
            discriminator: "expr(rawDataItemExists('energyProvider'))",
            property: new Property(class: ElectricCar::class)
        )
    ])]
    #[Hook(name: 'after_set_property', method: 'onCarsLoaded', args: ['##this', '#cars'])]
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
