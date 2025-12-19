<?php

declare(strict_types=1);

namespace Kassko\Sample;

class EmailDataSource
{
    public function getEmailData(): array
    {
        return ['email' => 'test@example.com'];
    }
}
