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
    public readonly ?string $exceptionOnNoValidDataSource;
    public readonly int $priority;

    /**
     * @param string|null $id Single source ID (with optional fallbacks)
     * @param array|null $fallbacks Fallback source IDs (requires id)
     * @param array|null $providers Aggregation providers
     * @param array|null $candidates Candidates with discriminator expressions.
     *                               Each candidate: ['id' => string, 'discriminator' => string, 'priority' => int (optional)]
     * @param string|null $exceptionOnNoValidDataSource Exception class for fallbacks
     * @param int $priority Base priority for hydration
     */
    public function __construct(
        ?string $id = null,
        ?array $fallbacks = null,
        ?array $providers = null,
        ?array $candidates = null,
        ?string $exceptionOnNoValidDataSource = null,
        int $priority = 0
    ) {
        // Count how many "modes" are set
        $modesSet = 0;
        if ($id !== null) $modesSet++;
        if ($providers !== null) $modesSet++;
        if ($candidates !== null) $modesSet++;

        // Build-time validation: exactly one mode must be set
        if ($modesSet === 0) {
            throw new \InvalidArgumentException('DataSourceRef requires one of: id, providers, or candidates');
        }
        if ($modesSet > 1) {
            throw new \InvalidArgumentException('DataSourceRef: id, providers, and candidates are mutually exclusive. Use only one.');
        }

        // Build-time validation: fallbacks only valid with id
        if ($fallbacks !== null && $id === null) {
            throw new \InvalidArgumentException('DataSourceRef: fallbacks can only be used with id');
        }

        // Build-time validation: exceptionOnNoValidDataSource requires fallbacks
        if ($exceptionOnNoValidDataSource !== null && $fallbacks === null) {
            throw new \InvalidArgumentException('DataSourceRef: exceptionOnNoValidDataSource requires fallbacks to be set');
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
                if (!isset($candidate['discriminator'])) {
                    throw new \InvalidArgumentException(sprintf('DataSourceRef: candidate at index %d must have a "discriminator" key', $index));
                }
            }
        }

        $this->id = $id;
        $this->fallbacks = $fallbacks;
        $this->providers = $providers;
        $this->candidates = $candidates;
        $this->exceptionOnNoValidDataSource = $exceptionOnNoValidDataSource;
        $this->priority = $priority;
    }
}
