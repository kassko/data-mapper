<?php

declare(strict_types=1);

namespace Kassko\Sample;

class PersonDataSource
{
    public function getData(int $id): array
    {
        return match($id) {
            1 => ['name' => 'foo', 'email' => 'foo@aaa.com', 'first_name' => 'Foo', 'car_id' => 100],
            2 => ['name' => 'bar', 'email' => 'bar@bbb.com', 'first_name' => 'Bar', 'car_id' => 200],
            default => ['name' => 'baz', 'email' => 'baz@ccc.com', 'first_name' => 'Baz', 'car_id' => 300],
        };
    }
}
