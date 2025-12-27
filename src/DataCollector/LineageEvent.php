<?php

declare(strict_types=1);

namespace Kassko\DataMapper\DataCollector;

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
        public readonly int $depth
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'objectClass' => $this->objectClass,
            'propertyName' => $this->propertyName,
            'source' => $this->source,
            'originalValue' => $this->serializeValue($this->originalValue),
            'finalValue' => $this->serializeValue($this->finalValue),
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp,
            'depth' => $this->depth,
        ];
    }

    private function serializeValue(mixed $value): mixed
    {
        if (is_object($value)) {
            return sprintf('[object %s]', get_class($value));
        }
        if (is_array($value)) {
            if (count($value) > 10) {
                return sprintf('[array with %d items]', count($value));
            }
            return array_map(fn($v) => $this->serializeValue($v), $value);
        }
        return $value;
    }
}
