<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Field;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new DataSource(
        id: 'personSource',
        class: PersonDataSource::class,
        method: 'getData',
        args: ['#id'],
        supplySeveralFields: true
    )
])]
class PersonWithStore
{
    use LoadableTrait;

    private int $id;

    #[DataSourceRef(id: 'personSource')]
    #[Field(name: 'first_name')]
    private ?string $firstName = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personSource')]
    private ?string $email = null;

    // This property should NOT be hydrated (no DataSourceRef)
    private ?int $age = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }

    public function getAge(): ?int
    {
        $this->loadProperty('age');
        return $this->age;
    }
}
