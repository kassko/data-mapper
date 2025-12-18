<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Person
{
    use LoadableTrait;

    private int $id;

    #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
    private ?string $name = null;

    #[DataSource(class: PersonDataSource::class, method: 'getData', args: ['#id'])]
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
