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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Collector for attribute cascading events.
 * 
 * This collector tracks events during PHP 8 attribute inheritance/cascading:
 * - DataSourcesStore merging from parent classes and traits
 * - PropertyConfigStore merging from parent classes and traits
 * - Property attribute overrides when child shadows parent property
 * - DataSource ID conflicts during store merging
 * - PropertyConfig ID conflicts during store merging
 * 
 * @experimental This is an alpha feature
 */
final class AttributeCascadeCollector
{
    /** @var CascadeEvent[] */
    private array $events = [];

    private bool $enabled = false;

    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Enable cascade event collection.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable cascade event collection.
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
     * Record a DataSourcesStore merge event.
     * 
     * @param string $targetClass The class receiving the merged store
     * @param string $sourceClass The class/trait providing the store
     * @param string $sourceType 'parent' or 'trait'
     * @param int $sourcesCount Number of sources merged
     */
    public function recordDataSourcesStoreMerge(
        string $targetClass,
        string $sourceClass,
        string $sourceType,
        int $sourcesCount
    ): void {
        $this->logger->info('DataSourcesStore merged from {sourceType}', [
            'targetClass' => $targetClass,
            'sourceClass' => $sourceClass,
            'sourceType' => $sourceType,
            'sourcesCount' => $sourcesCount,
        ]);

        if (!$this->enabled) {
            return;
        }

        $this->events[] = new CascadeEvent(
            type: CascadeEvent::TYPE_DATASOURCES_STORE_MERGE,
            targetClass: $targetClass,
            sourceClass: $sourceClass,
            sourceType: $sourceType,
            metadata: ['sourcesCount' => $sourcesCount]
        );
    }

    /**
     * Record a PropertyConfigStore merge event.
     * 
     * @param string $targetClass The class receiving the merged store
     * @param string $sourceClass The class/trait providing the store
     * @param string $sourceType 'parent' or 'trait'
     * @param int $configsCount Number of configs merged
     */
    public function recordPropertyConfigStoreMerge(
        string $targetClass,
        string $sourceClass,
        string $sourceType,
        int $configsCount
    ): void {
        $this->logger->info('PropertyConfigStore merged from {sourceType}', [
            'targetClass' => $targetClass,
            'sourceClass' => $sourceClass,
            'sourceType' => $sourceType,
            'configsCount' => $configsCount,
        ]);

        if (!$this->enabled) {
            return;
        }

        $this->events[] = new CascadeEvent(
            type: CascadeEvent::TYPE_PROPERTY_CONFIG_STORE_MERGE,
            targetClass: $targetClass,
            sourceClass: $sourceClass,
            sourceType: $sourceType,
            metadata: ['configsCount' => $configsCount]
        );
    }

    /**
     * Record a DataSource ID conflict (child overrides parent/trait).
     * 
     * @param string $targetClass The class with the conflict
     * @param string $sourceClass The class/trait being overridden
     * @param string $sourceType 'parent' or 'trait'
     * @param string $dataSourceId The conflicting DataSource ID
     */
    public function recordDataSourceIdConflict(
        string $targetClass,
        string $sourceClass,
        string $sourceType,
        string $dataSourceId
    ): void {
        $this->logger->warning('DataSource ID conflict: {dataSourceId} in {targetClass} overrides {sourceType} {sourceClass}', [
            'targetClass' => $targetClass,
            'sourceClass' => $sourceClass,
            'sourceType' => $sourceType,
            'dataSourceId' => $dataSourceId,
        ]);

        if (!$this->enabled) {
            return;
        }

        $this->events[] = new CascadeEvent(
            type: CascadeEvent::TYPE_DATASOURCE_ID_CONFLICT,
            targetClass: $targetClass,
            sourceClass: $sourceClass,
            sourceType: $sourceType,
            metadata: ['dataSourceId' => $dataSourceId]
        );
    }

    /**
     * Record a PropertyConfig ID conflict (child overrides parent/trait).
     * 
     * @param string $targetClass The class with the conflict
     * @param string $sourceClass The class/trait being overridden
     * @param string $sourceType 'parent' or 'trait'
     * @param string $configId The conflicting PropertyConfig ID
     */
    public function recordPropertyConfigIdConflict(
        string $targetClass,
        string $sourceClass,
        string $sourceType,
        string $configId
    ): void {
        $this->logger->warning('PropertyConfig ID conflict: {configId} in {targetClass} overrides {sourceType} {sourceClass}', [
            'targetClass' => $targetClass,
            'sourceClass' => $sourceClass,
            'sourceType' => $sourceType,
            'configId' => $configId,
        ]);

        if (!$this->enabled) {
            return;
        }

        $this->events[] = new CascadeEvent(
            type: CascadeEvent::TYPE_PROPERTY_CONFIG_ID_CONFLICT,
            targetClass: $targetClass,
            sourceClass: $sourceClass,
            sourceType: $sourceType,
            metadata: ['configId' => $configId]
        );
    }

    /**
     * Record a property attribute override (child shadows parent).
     * 
     * @param string $targetClass The child class
     * @param string $sourceClass The parent class
     * @param string $propertyName The property name being shadowed
     */
    public function recordPropertyAttributeOverride(
        string $targetClass,
        string $sourceClass,
        string $propertyName
    ): void {
        $this->logger->warning('Property {propertyName} in {targetClass} shadows parent {sourceClass} - using child attributes only', [
            'targetClass' => $targetClass,
            'sourceClass' => $sourceClass,
            'propertyName' => $propertyName,
        ]);

        if (!$this->enabled) {
            return;
        }

        $this->events[] = new CascadeEvent(
            type: CascadeEvent::TYPE_PROPERTY_ATTRIBUTE_OVERRIDE,
            targetClass: $targetClass,
            sourceClass: $sourceClass,
            sourceType: 'parent',
            metadata: ['propertyName' => $propertyName]
        );
    }

    /**
     * Get all collected cascade events.
     * 
     * @return CascadeEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Get events filtered by type.
     * 
     * @param string $type One of CascadeEvent::TYPE_* constants
     * @return CascadeEvent[]
     */
    public function getEventsByType(string $type): array
    {
        return array_filter($this->events, fn($e) => $e->type === $type);
    }

    /**
     * Clear all collected events.
     */
    public function reset(): void
    {
        $this->events = [];
    }

    /**
     * Get a summary of cascade events.
     * 
     * @return array{
     *     dataSourcesStoreMerges: int,
     *     propertyConfigStoreMerges: int,
     *     dataSourceIdConflicts: int,
     *     propertyConfigIdConflicts: int,
     *     propertyAttributeOverrides: int,
     *     total: int
     * }
     */
    public function getSummary(): array
    {
        return [
            'dataSourcesStoreMerges' => count($this->getEventsByType(CascadeEvent::TYPE_DATASOURCES_STORE_MERGE)),
            'propertyConfigStoreMerges' => count($this->getEventsByType(CascadeEvent::TYPE_PROPERTY_CONFIG_STORE_MERGE)),
            'dataSourceIdConflicts' => count($this->getEventsByType(CascadeEvent::TYPE_DATASOURCE_ID_CONFLICT)),
            'propertyConfigIdConflicts' => count($this->getEventsByType(CascadeEvent::TYPE_PROPERTY_CONFIG_ID_CONFLICT)),
            'propertyAttributeOverrides' => count($this->getEventsByType(CascadeEvent::TYPE_PROPERTY_ATTRIBUTE_OVERRIDE)),
            'total' => count($this->events),
        ];
    }
}
