<?php

declare(strict_types=1);

namespace Kassko\Sample;

class TestValidationService
{
    public array $validatedEmails = [];
    
    public function validateEmail(object $obj, ?string $email): void
    {
        $this->validatedEmails[] = $email;
    }
}
