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
 * Collector for data lineage and audit information.
 * 
 * This collector traces how data flows and transforms during hydration:
 * - DataSource calls and their results
 * - Property hydrations and transformations
 * - Decision points (where data is skipped due to priority, scope, lock, etc.)
 * - Hook executions and their effects
 * - Custom hydrator invocations
 * - Context changes
 * 
 * The collected data can be used by debugging tools (like Symfony Profiler)
 * to visualize the data flow and help diagnose hydration issues.
 * 
 * @experimental This is a beta feature
 */
final class DataLineageCollector
{
    /** @var LineageEvent[] */
    private array $events = [];

    /** @var bool */
    private bool $enabled = false;

    /** @var int Current hydration depth */
    private int $currentDepth = 0;

    /** @var float Start time of collection */
    private float $startTime;

    /** @var string|null Current flow ID for grouping related events */
    private ?string $currentFlowId = null;

    /** @var string[] Stack of flow IDs for nested flows */
    private array $flowStack = [];

    /** @var array<string, SensitiveLevel> Global sensitive keys configuration */
    private array $sensitiveKeys;

    /** @var SensitiveLevel Default sensitive level for all properties */
    private SensitiveLevel $defaultSensitiveLevel;

    /**
     * @param array<string, SensitiveLevel> $sensitiveKeys Global sensitive keys configuration
     * @param SensitiveLevel $defaultSensitiveLevel Default sensitive level for all properties
     */
    public function __construct(
        array $sensitiveKeys = [],
        SensitiveLevel $defaultSensitiveLevel = SensitiveLevel::SHOW
    ) {
        $this->startTime = microtime(true);
        $this->sensitiveKeys = $sensitiveKeys;
        $this->defaultSensitiveLevel = $defaultSensitiveLevel;
    }

    /**
     * Enable data collection.
     */
    public function enable(): void
    {
        $this->enabled = true;
        $this->startTime = microtime(true);
    }

    /**
     * Disable data collection.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if collection is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Increment the current depth (when descending into nested objects).
     */
    public function incrementDepth(): void
    {
        $this->currentDepth++;
    }

    /**
     * Decrement the current depth (when returning from nested objects).
     */
    public function decrementDepth(): void
    {
        if ($this->currentDepth > 0) {
            $this->currentDepth--;
        }
    }

    /**
     * Get the current hydration depth.
     */
    public function getCurrentDepth(): int
    {
        return $this->currentDepth;
    }

    /**
     * Start a new flow for grouping related events.
     * 
     * @param string $objectClass The class being hydrated
     * @param string|null $label Optional descriptive label for the flow
     * @return string The generated flow ID
     */
    public function startFlow(string $objectClass, ?string $label = null): string
    {
        $flowId = uniqid('flow_', true);
        
        // Push current flow onto stack if any
        if ($this->currentFlowId !== null) {
            $this->flowStack[] = $this->currentFlowId;
        }
        
        $this->currentFlowId = $flowId;
        
        if ($this->enabled) {
            $this->events[] = new LineageEvent(
                type: LineageEvent::TYPE_FLOW_START,
                objectClass: $objectClass,
                propertyName: null,
                source: null,
                originalValue: null,
                finalValue: null,
                reason: $label,
                metadata: [
                    'parentFlowId' => $this->flowStack[count($this->flowStack) - 1] ?? null,
                ],
                timestamp: microtime(true) - $this->startTime,
                depth: $this->currentDepth,
                flowId: $flowId,
                datetime: $this->createDatetime()
            );
        }
        
        return $flowId;
    }

    /**
     * End the current flow.
     * 
     * @param string|null $result Optional result/summary of the flow
     */
    public function endFlow(?string $result = null): void
    {
        if ($this->currentFlowId === null) {
            return;
        }
        
        $endingFlowId = $this->currentFlowId;
        
        if ($this->enabled) {
            $this->events[] = new LineageEvent(
                type: LineageEvent::TYPE_FLOW_END,
                objectClass: '',
                propertyName: null,
                source: null,
                originalValue: null,
                finalValue: null,
                reason: $result,
                metadata: [],
                timestamp: microtime(true) - $this->startTime,
                depth: $this->currentDepth,
                flowId: $endingFlowId,
                datetime: $this->createDatetime()
            );
        }
        
        // Restore parent flow if any
        $this->currentFlowId = array_pop($this->flowStack);
    }

    /**
     * Get the current flow ID.
     */
    public function getCurrentFlowId(): ?string
    {
        return $this->currentFlowId;
    }

    /**
     * Create the current datetime for events.
     */
    private function createDatetime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /**
     * Record a DataSource call.
     * 
     * @param array<string, SensitiveLevel> $sensitiveKeys Keys to mask in the result (exact name or regex pattern)
     */
    public function recordDataSourceCall(
        string $objectClass,
        string $sourceClass,
        string $sourceMethod,
        array $args,
        mixed $result,
        ?string $sourceId = null,
        array $sensitiveKeys = []
    ): void {
        if (!$this->enabled) {
            return;
        }

        // Apply sensitive masking to result
        $maskedResult = $this->applySensitiveKeysToResult($result, $sensitiveKeys);

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_DATASOURCE_CALL,
            objectClass: $objectClass,
            propertyName: null,
            source: sprintf('%s::%s', $sourceClass, $sourceMethod),
            originalValue: $args,
            finalValue: $maskedResult,
            reason: null,
            metadata: [
                'sourceId' => $sourceId,
                'argsCount' => count($args),
                'hasSensitiveKeys' => !empty($sensitiveKeys),
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a property hydration.
     */
    public function recordPropertyHydration(
        string $objectClass,
        string $propertyName,
        mixed $originalValue,
        mixed $finalValue,
        string $source,
        int $priority = 0,
        ?SensitiveLevel $sensitiveLevel = null
    ): void {
        if (!$this->enabled) {
            return;
        }

        // Resolve the sensitive level for this property
        $resolvedLevel = $this->getSensitiveLevelForProperty($propertyName, $sensitiveLevel);

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_PROPERTY_HYDRATION,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: $source,
            originalValue: $originalValue,
            finalValue: $finalValue,
            reason: null,
            metadata: [
                'priority' => $priority,
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            sensitiveLevel: $resolvedLevel,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a property being skipped (decision point).
     */
    public function recordPropertySkipped(
        string $objectClass,
        string $propertyName,
        string $reason,
        array $metadata = []
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_PROPERTY_SKIPPED,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: null,
            originalValue: null,
            finalValue: null,
            reason: $reason,
            metadata: $metadata,
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a property transformation (e.g., via hook).
     */
    public function recordPropertyTransformation(
        string $objectClass,
        string $propertyName,
        mixed $originalValue,
        mixed $transformedValue,
        string $transformer,
        string $transformerType = 'hook',
        ?SensitiveLevel $sensitiveLevel = null
    ): void {
        if (!$this->enabled) {
            return;
        }

        // Resolve the sensitive level for this property
        $resolvedLevel = $this->getSensitiveLevelForProperty($propertyName, $sensitiveLevel);

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_PROPERTY_TRANSFORMED,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: $transformer,
            originalValue: $originalValue,
            finalValue: $transformedValue,
            reason: null,
            metadata: [
                'transformerType' => $transformerType,
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            sensitiveLevel: $resolvedLevel,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a hook execution.
     */
    public function recordHookExecution(
        string $objectClass,
        string $hookType,
        string $hookMethod,
        ?string $hookClass,
        array $args
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_HOOK_EXECUTED,
            objectClass: $objectClass,
            propertyName: null,
            source: $hookClass ? sprintf('%s::%s', $hookClass, $hookMethod) : $hookMethod,
            originalValue: $args,
            finalValue: null,
            reason: null,
            metadata: [
                'hookType' => $hookType,
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a custom hydrator execution.
     */
    public function recordCustomHydrator(
        string $objectClass,
        string $propertyName,
        string $hydratorKey,
        mixed $result
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_CUSTOM_HYDRATOR,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: $hydratorKey,
            originalValue: null,
            finalValue: $result,
            reason: null,
            metadata: [],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a context value being set.
     */
    public function recordContextSet(
        string $objectClass,
        string $propertyName,
        string $key,
        mixed $value,
        ?SensitiveLevel $sensitiveLevel = null
    ): void {
        if (!$this->enabled) {
            return;
        }

        // Resolve the sensitive level for this property
        $resolvedLevel = $this->getSensitiveLevelForProperty($propertyName, $sensitiveLevel);

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_CONTEXT_SET,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: null,
            originalValue: null,
            finalValue: $value,
            reason: null,
            metadata: [
                'contextKey' => $key,
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            sensitiveLevel: $resolvedLevel,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a decision point (where data flow is affected by a condition).
     */
    public function recordDecisionPoint(
        string $objectClass,
        ?string $propertyName,
        string $decisionType,
        string $reason,
        array $metadata = []
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_DECISION_POINT,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: null,
            originalValue: null,
            finalValue: null,
            reason: $reason,
            metadata: array_merge(['decisionType' => $decisionType], $metadata),
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Record a candidate resolution (DataSourceRef with candidates).
     *
     * @param string $objectClass The class being hydrated
     * @param string $propertyName The property being hydrated
     * @param array $allCandidates All candidates that were evaluated
     * @param array|null $electedCandidate The candidate that was elected (null if none)
     * @param int $basePriority The base priority from DataSourceRef
     * @param int $effectivePriority The effective priority used (candidate's or base)
     */
    public function recordCandidateResolution(
        string $objectClass,
        string $propertyName,
        array $allCandidates,
        ?array $electedCandidate,
        int $basePriority,
        int $effectivePriority
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_CANDIDATE_RESOLUTION,
            objectClass: $objectClass,
            propertyName: $propertyName,
            source: $electedCandidate['id'] ?? null,
            originalValue: $allCandidates,
            finalValue: $electedCandidate,
            reason: $electedCandidate !== null ? 'candidate_elected' : 'no_candidate_matched',
            metadata: [
                'basePriority' => $basePriority,
                'effectivePriority' => $effectivePriority,
                'candidatesCount' => count($allCandidates),
                'electedCandidatePriority' => $electedCandidate['priority'] ?? null,
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth,
            flowId: $this->currentFlowId,
            datetime: $this->createDatetime()
        );
    }

    /**
     * Get all recorded events.
     * 
     * @return LineageEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Get events as arrays (for serialization).
     * 
     * @return array
     */
    public function getEventsAsArrays(): array
    {
        return array_map(fn(LineageEvent $event) => $event->toArray(), $this->events);
    }

    /**
     * Get events filtered by type.
     * 
     * @param string $type
     * @return LineageEvent[]
     */
    public function getEventsByType(string $type): array
    {
        return array_filter($this->events, fn(LineageEvent $event) => $event->type === $type);
    }

    /**
     * Get events for a specific property.
     * 
     * @param string $objectClass
     * @param string $propertyName
     * @return LineageEvent[]
     */
    public function getEventsForProperty(string $objectClass, string $propertyName): array
    {
        return array_filter(
            $this->events, 
            fn(LineageEvent $event) => 
                $event->objectClass === $objectClass && $event->propertyName === $propertyName
        );
    }

    /**
     * Get a summary of the data lineage.
     * 
     * @return array
     */
    public function getSummary(): array
    {
        $eventsByType = [];
        foreach ($this->events as $event) {
            $eventsByType[$event->type] = ($eventsByType[$event->type] ?? 0) + 1;
        }

        $skippedReasons = [];
        foreach ($this->getEventsByType(LineageEvent::TYPE_PROPERTY_SKIPPED) as $event) {
            $reason = $event->reason ?? 'unknown';
            $skippedReasons[$reason] = ($skippedReasons[$reason] ?? 0) + 1;
        }

        return [
            'totalEvents' => count($this->events),
            'eventsByType' => $eventsByType,
            'skippedReasons' => $skippedReasons,
            'maxDepth' => max(array_map(fn(LineageEvent $e) => $e->depth, $this->events) ?: [0]),
            'totalDuration' => count($this->events) > 0 
                ? $this->events[count($this->events) - 1]->timestamp 
                : 0,
        ];
    }

    /**
     * Get the sensitive level for a property.
     * 
     * @param string $propertyName The property name (can include class prefix like 'User.password')
     * @param SensitiveLevel|null $attributeLevel The level specified in the Property/PropertyConfig attribute
     * @return SensitiveLevel The resolved sensitive level
     */
    public function getSensitiveLevelForProperty(string $propertyName, ?SensitiveLevel $attributeLevel = null): SensitiveLevel
    {
        // Attribute-level configuration takes highest precedence
        if ($attributeLevel !== null) {
            return $attributeLevel;
        }

        // Check for exact match in global sensitive keys
        if (isset($this->sensitiveKeys[$propertyName])) {
            return $this->sensitiveKeys[$propertyName];
        }

        // Check for pattern matches (e.g., '*password*')
        foreach ($this->sensitiveKeys as $pattern => $level) {
            if ($this->matchesPattern($propertyName, $pattern)) {
                return $level;
            }
        }

        return $this->defaultSensitiveLevel;
    }

    /**
     * Apply sensitive level to a value.
     * 
     * @param mixed $value The value to process
     * @param string $propertyName The property name
     * @param SensitiveLevel|null $attributeLevel The level specified in the attribute
     * @return mixed The processed value
     */
    public function applySensitiveLevel(mixed $value, string $propertyName, ?SensitiveLevel $attributeLevel = null): mixed
    {
        $level = $this->getSensitiveLevelForProperty($propertyName, $attributeLevel);
        return $level->apply($value);
    }

    /**
     * Check if a property name matches a pattern.
     * 
     * Supports patterns like:
     * - 'password' - exact match
     * - '*password*' - contains 'password'
     * - 'password*' - starts with 'password'
     * - '*password' - ends with 'password'
     * - 'User.*' - all properties of User class
     * 
     * @param string $propertyName The property name to check
     * @param string $pattern The pattern to match against
     * @return bool True if the property matches the pattern
     */
    private function matchesPattern(string $propertyName, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = '/^' . str_replace(
            ['\\*', '\\?'],
            ['.*', '.'],
            preg_quote($pattern, '/')
        ) . '$/i';

        return preg_match($regex, $propertyName) === 1;
    }

    /**
     * Add a sensitive key at runtime.
     * 
     * @param string $key The property name or pattern
     * @param SensitiveLevel $level The sensitive level
     */
    public function addSensitiveKey(string $key, SensitiveLevel $level): void
    {
        $this->sensitiveKeys[$key] = $level;
    }

    /**
     * Remove a sensitive key at runtime.
     * 
     * @param string $key The property name or pattern to remove
     */
    public function removeSensitiveKey(string $key): void
    {
        unset($this->sensitiveKeys[$key]);
    }

    /**
     * Get all configured sensitive keys.
     * 
     * @return array<string, SensitiveLevel>
     */
    public function getSensitiveKeys(): array
    {
        return $this->sensitiveKeys;
    }

    /**
     * Get the default sensitive level.
     * 
     * @return SensitiveLevel
     */
    public function getDefaultSensitiveLevel(): SensitiveLevel
    {
        return $this->defaultSensitiveLevel;
    }

    /**
     * Set the default sensitive level.
     * 
     * @param SensitiveLevel $level
     */
    public function setDefaultSensitiveLevel(SensitiveLevel $level): void
    {
        $this->defaultSensitiveLevel = $level;
    }

    /**
     * Apply sensitive keys masking to a DataSource result.
     * 
     * @param mixed $result The result from the DataSource call
     * @param array<string, SensitiveLevel> $sensitiveKeys Keys to mask (exact name or pattern)
     * @return mixed The result with sensitive keys masked
     */
    private function applySensitiveKeysToResult(mixed $result, array $sensitiveKeys): mixed
    {
        if (empty($sensitiveKeys)) {
            return $result;
        }

        if (!is_array($result)) {
            return $result;
        }

        $masked = [];
        foreach ($result as $key => $value) {
            $level = $this->getSensitiveLevelForKey((string) $key, $sensitiveKeys);
            $masked[$key] = $level->apply($value);
        }

        return $masked;
    }

    /**
     * Get the sensitive level for a specific key in a DataSource result.
     * 
     * @param string $key The key name
     * @param array<string, SensitiveLevel> $sensitiveKeys The sensitive keys configuration
     * @return SensitiveLevel The resolved sensitive level
     */
    private function getSensitiveLevelForKey(string $key, array $sensitiveKeys): SensitiveLevel
    {
        // Check for exact match
        if (isset($sensitiveKeys[$key])) {
            return $sensitiveKeys[$key];
        }

        // Check for pattern matches
        foreach ($sensitiveKeys as $pattern => $level) {
            if ($this->matchesPattern($key, $pattern)) {
                return $level;
            }
        }

        return SensitiveLevel::SHOW;
    }

    /**
     * Clear all recorded events.
     */
    public function clear(): void
    {
        $this->events = [];
        $this->currentDepth = 0;
        $this->startTime = microtime(true);
    }

    /**
     * Reset the collector (clear events and reset state).
     */
    public function reset(): void
    {
        $this->clear();
        $this->enabled = false;
    }
}
