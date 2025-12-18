<?php

declare(strict_types=1);

namespace Kassko\Sample;

class PersonDataSource
{
    public function getData(int $id): array
    {
        return match($id) {
            1 => ['name' => 'foo', 'email' => 'foo@aaa.com'],
            2 => ['name' => 'bar', 'email' => 'bar@bbb.com'],
            default => ['name' => 'baz', 'email' => 'baz@ccc.com'],
        };
    }
}
