<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Sample\MappingStrategy;

/**
 * Custom mapping service for testing custom callable mapping strategies.
 */
class CustomMappingService
{
    /**
     * Custom mapping function that prefixes field names with "custom_".
     * 
     * @param string $propertyName The property name (camelCase)
     * @param array $data The source data
     * @param array $args Additional arguments
     * @return string The source field name
     */
    public function mapPropertyToSource(string $propertyName, array $data, array $args): string
    {
        // Convert camelCase to snake_case and prefix with "custom_"
        $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
        $customField = 'custom_' . $snakeCase;
        
        // Check if the custom field exists in data
        if (array_key_exists($customField, $data)) {
            return $customField;
        }
        
        // Fallback to snake_case without prefix
        if (array_key_exists($snakeCase, $data)) {
            return $snakeCase;
        }
        
        // Return the custom field (may be resolved later)
        return $customField;
    }
}
