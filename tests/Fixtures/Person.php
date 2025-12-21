<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[MultiPropDataSource(id: 'personData', class: PersonDataSource::class, method: 'getData', args: ['#id'])]
class Person
{
    use LoadableTrait;

    private int $id;

    #[DataSourceRef(id: 'personData')]
    private ?string $name = null;

    #[DataSourceRef(id: 'personData')]
    private ?string $email = null;

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

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }
}
