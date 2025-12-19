<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Registry;

final class ContextRegistry
{
    /** @var array<string, mixed> */
    private static array $context = [];

    public static function set(string $key, mixed $value): void
    {
        self::$context[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$context[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$context);
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function clear(): void
    {
        self::$context = [];
    }

    public static function all(): array
    {
        return self::$context;
    }
}
