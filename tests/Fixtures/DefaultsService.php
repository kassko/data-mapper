<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Fixtures;

class DefaultsService
{
    public function getDefaults(): array
    {
        return [
            'theme' => 'system',
            'language' => 'en',
            'timezone' => 'UTC',
            'notifications' => false,
        ];
    }

    public function getProductName(int $id): string
    {
        return "Default Product";
    }

    public function getProductData(int $id): array
    {
        return [
            'name' => "Default Product",
        ];
    }
}
