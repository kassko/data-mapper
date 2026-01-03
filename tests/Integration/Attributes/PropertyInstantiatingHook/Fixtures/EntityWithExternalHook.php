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

namespace Kassko\Sample\PropertyInstantiatingHook;

use Kassko\DataMapper\Attribute\PropertyInstantiatingHook;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Entity that uses an external service for the instantiating hook.
 */
#[PropertyInstantiatingHook(
    after_instantiating: 'trackInstantiation',
    class: InstantiationTracker::class,
    args: ['##object']
)]
class EntityWithExternalHook
{
    use LoadableTrait;

    private ?int $id = null;

    #[SinglePropDataSource(class: TrackedEntityDataSource::class, method: 'getData', args: ['#id'])]
    private ?string $name = null;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}
