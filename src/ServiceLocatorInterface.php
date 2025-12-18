<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

interface ServiceLocatorInterface
{
    /**
     * Check if this locator has a mapping for the given key.
     */
    public function has(string $key): bool;

    /**
     * Get the mapped value for the given key.
     * Returns the service ID (with @) or class name.
     */
    public function get(string $key): string;
}
