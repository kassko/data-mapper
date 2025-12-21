<?php

declare(strict_types=1);

namespace Kassko\Sample;

class TestLogService
{
    public array $logs = [];
    
    public function logChange(object $obj, string $propertyName, mixed $oldValue, mixed $newValue): void
    {
        $this->logs[] = [
            'property' => $propertyName,
            'old' => $oldValue,
            'new' => $newValue,
        ];
    }
}
