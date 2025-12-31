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

namespace Kassko\DataMapper\DataCollector;

use Kassko\DataMapper\Enum\SensitiveLevel;

/**
 * Represents a single event in the data lineage.
 */
final class LineageEvent
{
    public const TYPE_DATASOURCE_CALL = 'datasource_call';
    public const TYPE_PROPERTY_HYDRATION = 'property_hydration';
    public const TYPE_PROPERTY_SKIPPED = 'property_skipped';
    public const TYPE_PROPERTY_TRANSFORMED = 'property_transformed';
    public const TYPE_HOOK_EXECUTED = 'hook_executed';
    public const TYPE_CUSTOM_HYDRATOR = 'custom_hydrator';
    public const TYPE_CONTEXT_SET = 'context_set';
    public const TYPE_DECISION_POINT = 'decision_point';
    public const TYPE_CANDIDATE_RESOLUTION = 'candidate_resolution';
    public const TYPE_FLOW_START = 'flow_start';
    public const TYPE_FLOW_END = 'flow_end';

    public function __construct(
        public readonly string $type,
        public readonly string $objectClass,
        public readonly ?string $propertyName,
        public readonly ?string $source,
        public readonly mixed $originalValue,
        public readonly mixed $finalValue,
        public readonly ?string $reason,
        public readonly array $metadata,
        public readonly float $timestamp,
        public readonly int $depth,
        public readonly ?string $flowId = null,
        public readonly ?SensitiveLevel $sensitiveLevel = null,
        public readonly ?\DateTimeImmutable $datetime = null
    ) {}

    /**
     * Convert the event to an array, optionally applying sensitive level.
     * 
     * @param SensitiveLevel|null $overrideSensitiveLevel Override the event's sensitive level
     * @return array
     */
    public function toArray(?SensitiveLevel $overrideSensitiveLevel = null): array
    {
        $level = $overrideSensitiveLevel ?? $this->sensitiveLevel;
        
        return [
            'type' => $this->type,
            'objectClass' => $this->objectClass,
            'propertyName' => $this->propertyName,
            'source' => $this->source,
            'originalValue' => $this->serializeValue($this->originalValue, $level),
            'finalValue' => $this->serializeValue($this->finalValue, $level),
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp,
            'datetime' => $this->datetime?->format(\DateTimeInterface::ATOM),
            'depth' => $this->depth,
            'flowId' => $this->flowId,
            'sensitiveLevel' => $level?->value,
        ];
    }

    /**
     * Serialize a value for output, optionally applying sensitive level.
     * 
     * @param mixed $value The value to serialize
     * @param SensitiveLevel|null $sensitiveLevel The sensitive level to apply
     * @return mixed
     */
    private function serializeValue(mixed $value, ?SensitiveLevel $sensitiveLevel = null): mixed
    {
        // Apply sensitive level first if specified
        if ($sensitiveLevel !== null && $sensitiveLevel !== SensitiveLevel::SHOW) {
            return $sensitiveLevel->apply($value);
        }
        
        if (is_object($value)) {
            return sprintf('[object %s]', get_class($value));
        }
        if (is_array($value)) {
            if (count($value) > 10) {
                return sprintf('[array with %d items]', count($value));
            }
            return array_map(fn($v) => $this->serializeValue($v, $sensitiveLevel), $value);
        }
        return $value;
    }
}
