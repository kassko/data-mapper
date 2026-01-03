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

/**
 * Entity with multiple PropertyInstantiatingHook attributes.
 * Tests that multiple hooks can be applied and executed in order.
 */
#[PropertyInstantiatingHook(after_instantiating: 'firstHook', args: ['##object'])]
#[PropertyInstantiatingHook(after_instantiating: 'secondHook', args: ['##object'])]
class MultiHookEntity
{
    private array $hookCallOrder = [];
    private int $hookCallCount = 0;

    public function firstHook(self $entity): void
    {
        $this->hookCallCount++;
        $this->hookCallOrder[] = 'firstHook';
    }

    public function secondHook(self $entity): void
    {
        $this->hookCallCount++;
        $this->hookCallOrder[] = 'secondHook';
    }

    public function getHookCallOrder(): array
    {
        return $this->hookCallOrder;
    }

    public function getHookCallCount(): int
    {
        return $this->hookCallCount;
    }
}
