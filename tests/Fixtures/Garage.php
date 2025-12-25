<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\Attribute\PropertyCandidates;
use Kassko\DataMapper\Attribute\PropertyCandidate;
use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[PropertyInstantiatingHook(after_instantiating: 'onCreated', args: ['##object'])]
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
