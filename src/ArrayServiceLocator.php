<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

final class ArrayServiceLocator implements ServiceLocatorInterface
{
    /**
     * @param array<string, string> $map Keys to service IDs or class names
     */
    public function __construct(
        private array $map
    ) {}

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->map);
    }

    public function get(string $key): string
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException(sprintf('Key "%s" not found in locator', $key));
        }
        return $this->map[$key];
    }
}
