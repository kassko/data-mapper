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

#[Attribute(Attribute::TARGET_PROPERTY)]
final class DataSourceRef
{
    public readonly ?string $id;
    public readonly ?array $fallbacks;
    public readonly ?array $providers;
    public readonly ?array $candidates;
    public readonly ?array $defaultCandidate;
    public readonly ?string $exceptionOnNoValidFallback;
    public readonly bool $ignoreProviderOnNotFound;
    public readonly int $priority;
    public readonly bool $cascade;  // Whether this attribute cascades to child classes
    public readonly ?string $handleWhen;  // Optional expression to conditionally apply this data source ref
    public readonly bool $enabled;  // Whether this attribute is active

    /**
     * @param string|null $id Single source ID (with optional fallbacks)
     * @param array|null $fallbacks Fallback source IDs (requires id)
     * @param array|null $providers Aggregation providers
     * @param array|null $candidates Candidates with 'when' expressions.
     *                               Each candidate: ['id' => string, 'when' => string, 'priority' => int (optional)]
     * @param array|null $defaultCandidate Default candidate if no expression matches.
     *                                      Structure: ['id' => string, 'priority' => int (optional)]
     * @param string|null $exceptionOnNoValidFallback Exception class for fallbacks (thrown when all fallbacks fail)
     * @param bool $ignoreProviderOnNotFound If true, missing providers are silently skipped (requires providers)
     * @param int $priority Base priority for hydration
     * @param bool $cascade Whether this attribute cascades to child classes
     * @param string|null $handleWhen Optional expression to conditionally apply this data source ref.
     *                                If null (default), ref is always applied.
     *                                If expression evaluates to true, ref is applied.
     *                                If expression evaluates to false, ref is ignored.
     * @param bool $enabled Whether this attribute is active (disabled attributes are ignored)
     */
    public function __construct(
        ?string $id = null,
        ?array $fallbacks = null,
        ?array $providers = null,
        ?array $candidates = null,
        ?array $defaultCandidate = null,
        ?string $exceptionOnNoValidFallback = null,
        bool $ignoreProviderOnNotFound = false,
        int $priority = 0,
        bool $cascade = true,
        ?string $handleWhen = null,
        bool $enabled = true
    ) {
        // Count how many "modes" are set
        $modesSet = 0;
        if ($id !== null) $modesSet++;
        if ($providers !== null) $modesSet++;
        if ($candidates !== null || $defaultCandidate !== null) $modesSet++;

        // Build-time validation: exactly one mode must be set
        if ($modesSet === 0) {
            throw new \InvalidArgumentException('DataSourceRef requires one of: id, providers, or candidates');
        }
        if ($modesSet > 1) {
            throw new \InvalidArgumentException('DataSourceRef: id, providers, and candidates are mutually exclusive. Use only one.');
        }

        // Build-time validation: candidates and defaultCandidate must both be present or both absent
        if (($candidates !== null) !== ($defaultCandidate !== null)) {
            throw new \InvalidArgumentException('DataSourceRef: candidates and defaultCandidate must both be present or both absent');
        }

        // Build-time validation: when candidates mode, only priority can be present
        if ($candidates !== null) {
            if ($fallbacks !== null) {
                throw new \InvalidArgumentException('DataSourceRef: fallbacks cannot be used with candidates');
            }
            if ($exceptionOnNoValidFallback !== null) {
                throw new \InvalidArgumentException('DataSourceRef: exceptionOnNoValidFallback cannot be used with candidates');
            }
        }

        // Build-time validation: fallbacks only valid with id
        if ($fallbacks !== null && $id === null) {
            throw new \InvalidArgumentException('DataSourceRef: fallbacks can only be used with id');
        }

        // Build-time validation: exceptionOnNoValidFallback requires fallbacks
        if ($exceptionOnNoValidFallback !== null && $fallbacks === null) {
            throw new \InvalidArgumentException('DataSourceRef: exceptionOnNoValidFallback requires fallbacks to be set');
        }

        // Build-time validation: ignoreProviderOnNotFound requires providers
        if ($ignoreProviderOnNotFound && $providers === null) {
            throw new \InvalidArgumentException('DataSourceRef: ignoreProviderOnNotFound can only be used with providers');
        }

        // Build-time validation: candidates structure
        if ($candidates !== null) {
            foreach ($candidates as $index => $candidate) {
                if (!is_array($candidate)) {
                    throw new \InvalidArgumentException(sprintf('DataSourceRef: candidate at index %d must be an array', $index));
                }
                if (!isset($candidate['id'])) {
                    throw new \InvalidArgumentException(sprintf('DataSourceRef: candidate at index %d must have an "id" key', $index));
                }
                if (!isset($candidate['when'])) {
                    throw new \InvalidArgumentException(sprintf('DataSourceRef: candidate at index %d must have a "when" key', $index));
                }
            }
        }

        // Build-time validation: defaultCandidate structure
        if ($defaultCandidate !== null) {
            if (!is_array($defaultCandidate)) {
                throw new \InvalidArgumentException('DataSourceRef: defaultCandidate must be an array');
            }
            if (!isset($defaultCandidate['id'])) {
                throw new \InvalidArgumentException('DataSourceRef: defaultCandidate must have an "id" key');
            }
        }

        $this->id = $id;
        $this->fallbacks = $fallbacks;
        $this->providers = $providers;
        $this->candidates = $candidates;
        $this->defaultCandidate = $defaultCandidate;
        $this->exceptionOnNoValidFallback = $exceptionOnNoValidFallback;
        $this->ignoreProviderOnNotFound = $ignoreProviderOnNotFound;
        $this->priority = $priority;
        $this->cascade = $cascade;
        $this->handleWhen = $handleWhen;
        $this->enabled = $enabled;
    }
}
