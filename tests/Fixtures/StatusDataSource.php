<?php

declare(strict_types=1);

namespace Kassko\Sample;

class StatusDataSource
{
    public function getStatusData(): array
    {
        return ['status' => 'published'];
    }
}
