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
use Kassko\DataMapper\Enum\SensitiveLevel;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Property
{
    public function __construct(
        public readonly ?string $name = null,      // Key name in data array
        public readonly ?string $class = null,     // Class for nested object hydration
        public readonly ?string $expand = null,    // Comma-separated props to expand
        public readonly ?string $noExpand = null,  // Comma-separated props to NOT expand
        public readonly ?array $mapping = null,    // Instance-specific key mapping
        public readonly ?string $config = null,    // Reference to PropertyConfig by ID
        /** @var array<array{id: string, rule: string}>|null Config candidates with rules */
        public readonly ?array $configCandidates = null,
        public readonly ?string $defaultConfigCandidate = null,  // Default config ID when no rule matches
        public readonly ?SensitiveLevel $sensitiveLevel = null,  // Sensitivity level for lineage collection
    ) {
        // Validation: mapping requires class to be set
        if ($mapping !== null && $class === null) {
            throw new \InvalidArgumentException('Property: mapping can only be set when class is also specified');
        }

        // Validation: config is mutually exclusive with class, expand, noExpand, mapping
        if ($config !== null && ($class !== null || $expand !== null || $noExpand !== null || $mapping !== null)) {
            throw new \InvalidArgumentException(
                'Property: config is mutually exclusive with class, expand, noExpand, and mapping'
            );
        }

        // Validation: configCandidates is mutually exclusive with class, expand, noExpand, mapping, config
        if ($configCandidates !== null && ($class !== null || $expand !== null || $noExpand !== null || $mapping !== null || $config !== null)) {
            throw new \InvalidArgumentException(
                'Property: configCandidates is mutually exclusive with class, expand, noExpand, mapping, and config'
            );
        }

        // Validation: configCandidates and defaultConfigCandidate must both be present or both absent
        if (($configCandidates === null) !== ($defaultConfigCandidate === null)) {
            throw new \InvalidArgumentException(
                'Property: configCandidates and defaultConfigCandidate must both be present or both absent'
            );
        }

        // Validation: each configCandidate must have 'id' and 'rule' keys
        if ($configCandidates !== null) {
            foreach ($configCandidates as $index => $candidate) {
                if (!is_array($candidate) || !isset($candidate['id']) || !isset($candidate['rule'])) {
                    throw new \InvalidArgumentException(
                        sprintf('Property: each configCandidate must be an array with "id" and "rule" keys (index %d)', $index)
                    );
                }
            }
        }
    }
}
