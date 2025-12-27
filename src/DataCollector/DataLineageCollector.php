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
 * @experimental This is an alpha feature
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

    public function __construct()
    {
        $this->startTime = microtime(true);
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
     * Record a DataSource call.
     */
    public function recordDataSourceCall(
        string $objectClass,
        string $sourceClass,
        string $sourceMethod,
        array $args,
        mixed $result,
        ?string $sourceId = null
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = new LineageEvent(
            type: LineageEvent::TYPE_DATASOURCE_CALL,
            objectClass: $objectClass,
            propertyName: null,
            source: sprintf('%s::%s', $sourceClass, $sourceMethod),
            originalValue: $args,
            finalValue: $result,
            reason: null,
            metadata: [
                'sourceId' => $sourceId,
                'argsCount' => count($args),
            ],
            timestamp: microtime(true) - $this->startTime,
            depth: $this->currentDepth
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
        int $priority = 0
    ): void {
        if (!$this->enabled) {
            return;
        }

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
            depth: $this->currentDepth
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
            depth: $this->currentDepth
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
        string $transformerType = 'hook'
    ): void {
        if (!$this->enabled) {
            return;
        }

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
            depth: $this->currentDepth
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
            depth: $this->currentDepth
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
            depth: $this->currentDepth
        );
    }

    /**
     * Record a context value being set.
     */
    public function recordContextSet(
        string $objectClass,
        string $propertyName,
        string $key,
        mixed $value
    ): void {
        if (!$this->enabled) {
            return;
        }

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
            depth: $this->currentDepth
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
            depth: $this->currentDepth
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
            depth: $this->currentDepth
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
