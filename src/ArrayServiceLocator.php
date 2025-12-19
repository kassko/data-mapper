<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\Exception\NotFoundException;

final class ArrayServiceLocator implements ServiceLocatorInterface
{
    /**
     * @param array<string, string> $map Keys to service IDs or class names
     */
    public function __construct(
        private array $map = []
    ) {}

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->map);
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Key \"$id\" not found in locator");
        }
        return $this->map[$id];
    }
}

