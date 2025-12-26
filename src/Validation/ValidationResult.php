<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Validation;

/**
 * Represents the validation result for a class
 */
class ValidationResult
{
    public function __construct(
        public readonly string $className,
        public readonly array $errors,
        public readonly array $warnings
    ) {}

    /**
     * Check if validation passed (no errors)
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if there are any warnings
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get a formatted string representation
     */
    public function format(): string
    {
        $output = "Class: {$this->className}\n";

        if ($this->isValid()) {
            $output .= "  ✓ Valid";
            if ($this->hasWarnings()) {
                $output .= " (with warnings)";
            }
            $output .= "\n";
        } else {
            $output .= "  ✗ Invalid\n";
        }

        if (!empty($this->errors)) {
            $output .= "\nErrors:\n";
            foreach ($this->errors as $error) {
                $output .= "  • {$error}\n";
            }
        }

        if (!empty($this->warnings)) {
            $output .= "\nWarnings:\n";
            foreach ($this->warnings as $warning) {
                $output .= "  ⚠ {$warning}\n";
            }
        }

        return $output;
    }
}
