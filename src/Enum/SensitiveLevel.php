<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Enum;

/**
 * Enum for sensitive data handling levels.
 * 
 * Controls how sensitive data is displayed in lineage collection and debugging output.
 */
enum SensitiveLevel: string
{
    /**
     * Show the full value (no masking).
     * Use with caution - only for non-sensitive data or secured environments.
     */
    case SHOW = 'show';

    /**
     * Completely hide the value, showing only "[SENSITIVE]".
     * Recommended for passwords, tokens, secrets.
     */
    case HIDE = 'hide';

    /**
     * Mask the value, showing only first/last characters.
     * Example: "secretpassword" becomes "se**********rd"
     * Good balance between debugging and security.
     */
    case MASK = 'mask';

    /**
     * Show only the type and length of the value.
     * Example: "[string(14)]" for "secretpassword"
     */
    case TYPE_ONLY = 'type_only';

    /**
     * Apply masking to the value based on the level.
     */
    public function apply(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($this) {
            self::SHOW => $value,
            self::HIDE => '[SENSITIVE]',
            self::MASK => $this->maskValue($value),
            self::TYPE_ONLY => $this->formatTypeOnly($value),
        };
    }

    private function maskValue(mixed $value): string
    {
        if (is_string($value)) {
            $length = strlen($value);
            if ($length <= 4) {
                return str_repeat('*', $length);
            }
            return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
        }
        
        if (is_numeric($value)) {
            $str = (string) $value;
            $length = strlen($str);
            if ($length <= 4) {
                return str_repeat('*', $length);
            }
            return substr($str, 0, 2) . str_repeat('*', $length - 4) . substr($str, -2);
        }
        
        if (is_array($value)) {
            return sprintf('[array(%d items) - MASKED]', count($value));
        }
        
        if (is_object($value)) {
            return sprintf('[object %s - MASKED]', get_class($value));
        }
        
        return '[MASKED]';
    }

    private function formatTypeOnly(mixed $value): string
    {
        $type = gettype($value);
        
        if (is_string($value)) {
            return sprintf('[string(%d)]', strlen($value));
        }
        
        if (is_array($value)) {
            return sprintf('[array(%d)]', count($value));
        }
        
        if (is_object($value)) {
            return sprintf('[object %s]', get_class($value));
        }
        
        return sprintf('[%s]', $type);
    }
}
