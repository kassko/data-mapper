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

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Adds context entries for hydration.
 * 
 * Context supports three levels:
 * - Application context: Shared across all hydration sessions
 * - Hydration context: Shared within one hydration session
 * - Object instance context: Specific to one object instance (values may depend on object data)
 * 
 * Each entry can be:
 * - A simple key-value pair: ['key' => 'myKey', 'value' => 'myValue']
 * - A method result capture: ['key' => 'myData', 'class' => 'my.service', 'method' => 'getData', 'args' => ['#id']]
 * 
 * Context entries are evaluated in order, so a later entry can reference an earlier one via expressions.
 * 
 * Usage:
 * ```php
 * #[Context(
 *     ['key' => 'staticValue', 'value' => 'hello'],
 *     ['key' => 'personData', 'class' => 'person.service', 'method' => 'fetchData', 'args' => ['#id']],
 *     ['key' => 'derivedData', 'class' => 'other.service', 'method' => 'process', 'args' => ["expr(context('personData'))"]],
 * )]
 * class Person
 * {
 *     // ...
 * }
 * ```
 * 
 * Access in expressions:
 * - `context('key')` - returns the value or null if not found (logs warning)
 * - `contextKeyExists('key')` - returns true/false
 * 
 * Using ##object in class to reference a method on the current object:
 * ```php
 * #[MethodAlias(name: 'checkKey', class: '##object', method: 'keyExists')]
 * #[Context(
 *     ['key' => 'rawData', 'class' => 'data.source', 'method' => 'fetch', 'args' => ['#id']],
 * )]
 * class Person
 * {
 *     private function keyExists(array $data, string $key): bool
 *     {
 *         return isset($data[$key]);
 *     }
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Context
{
    /** @var array<array{key: string, value?: mixed, class?: string, method?: string, args?: array}> Context entries */
    public readonly array $entries;
    public readonly bool $cascade;  // Whether this attribute cascades to child classes
    public readonly bool $enabled;  // Whether this attribute is active

    /**
     * @param array ...$entries Each entry must have a 'key' and either 'value' or 'class'+'method'
     */
    public function __construct(array ...$entries)
    {
        $lastEntry = end($entries);
        $cascade = true;
        $enabled = true;
        
        // Check if the last entry is a configuration (cascade or enabled)
        if ($lastEntry !== false && count($lastEntry) <= 2) {
            $isConfig = isset($lastEntry['cascade']) || isset($lastEntry['enabled']);
            if ($isConfig) {
                if (isset($lastEntry['cascade'])) {
                    $cascade = (bool) $lastEntry['cascade'];
                }
                if (isset($lastEntry['enabled'])) {
                    $enabled = (bool) $lastEntry['enabled'];
                }
                array_pop($entries);
            }
        }
        
        // Validate each entry
        foreach ($entries as $index => $entry) {
            if (!isset($entry['key'])) {
                throw new \InvalidArgumentException(
                    sprintf('Context: entry at index %d must have a "key"', $index)
                );
            }
            
            $hasValue = array_key_exists('value', $entry);
            $hasClass = isset($entry['class']);
            $hasMethod = isset($entry['method']);
            
            if (!$hasValue && !($hasClass && $hasMethod)) {
                throw new \InvalidArgumentException(
                    sprintf('Context: entry "%s" must have either "value" or both "class" and "method"', $entry['key'])
                );
            }
            
            if ($hasValue && ($hasClass || $hasMethod)) {
                throw new \InvalidArgumentException(
                    sprintf('Context: entry "%s" cannot have both "value" and "class"/"method"', $entry['key'])
                );
            }
        }
        
        $this->entries = $entries;
        $this->cascade = $cascade;
        $this->enabled = $enabled;
    }
}

