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

namespace Kassko\DataMapper\Tests\Unit\DataCollector;

use Kassko\DataMapper\DataCollector\DataLineageCollector;
use Kassko\DataMapper\DataCollector\LineageEvent;
use PHPUnit\Framework\TestCase;

class DataLineageCollectorTest extends TestCase
{
    private DataLineageCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new DataLineageCollector();
    }

    public function testCollectorIsDisabledByDefault(): void
    {
        $this->assertFalse($this->collector->isEnabled());
    }

    public function testEnableAndDisable(): void
    {
        $this->collector->enable();
        $this->assertTrue($this->collector->isEnabled());

        $this->collector->disable();
        $this->assertFalse($this->collector->isEnabled());
    }

    public function testEventsAreNotRecordedWhenDisabled(): void
    {
        $this->collector->recordDataSourceCall(
            'TestClass',
            'SourceClass',
            'getData',
            [],
            ['result' => 'data'],
            'testSource'
        );

        $this->assertEmpty($this->collector->getEvents());
    }

    public function testRecordDataSourceCall(): void
    {
        $this->collector->enable();

        $this->collector->recordDataSourceCall(
            'TestClass',
            'SourceClass',
            'getData',
            ['arg1'],
            ['result' => 'data'],
            'testSource'
        );

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        $this->assertEquals(LineageEvent::TYPE_DATASOURCE_CALL, $events[0]->type);
        $this->assertEquals('TestClass', $events[0]->objectClass);
        $this->assertEquals('SourceClass::getData', $events[0]->source);
    }

    public function testRecordPropertyHydration(): void
    {
        $this->collector->enable();

        $this->collector->recordPropertyHydration(
            'TestClass',
            'testProperty',
            'original',
            'hydrated',
            'testSource',
            5
        );

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        $this->assertEquals(LineageEvent::TYPE_PROPERTY_HYDRATION, $events[0]->type);
        $this->assertEquals('testProperty', $events[0]->propertyName);
        $this->assertEquals('original', $events[0]->originalValue);
        $this->assertEquals('hydrated', $events[0]->finalValue);
    }

    public function testRecordPropertySkipped(): void
    {
        $this->collector->enable();

        $this->collector->recordPropertySkipped(
            'TestClass',
            'testProperty',
            'locked',
            ['extra' => 'info']
        );

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        $this->assertEquals(LineageEvent::TYPE_PROPERTY_SKIPPED, $events[0]->type);
        $this->assertEquals('locked', $events[0]->reason);
    }

    public function testRecordHookExecution(): void
    {
        $this->collector->enable();

        $this->collector->recordHookExecution(
            'TestClass',
            'property_setting',
            'beforeSet',
            'HookClass',
            ['arg1']
        );

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        $this->assertEquals(LineageEvent::TYPE_HOOK_EXECUTED, $events[0]->type);
        $this->assertEquals('HookClass::beforeSet', $events[0]->source);
    }

    public function testRecordContextSet(): void
    {
        $this->collector->enable();

        $this->collector->recordContextSet(
            'TestClass',
            'testProperty',
            'contextKey',
            'contextValue'
        );

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        $this->assertEquals(LineageEvent::TYPE_CONTEXT_SET, $events[0]->type);
        $this->assertEquals('contextValue', $events[0]->finalValue);
        $this->assertEquals('contextKey', $events[0]->metadata['contextKey']);
    }

    public function testDepthTracking(): void
    {
        $this->collector->enable();
        $this->assertEquals(0, $this->collector->getCurrentDepth());

        $this->collector->incrementDepth();
        $this->assertEquals(1, $this->collector->getCurrentDepth());

        $this->collector->recordPropertyHydration('Test', 'prop', null, 'value', 'source');

        $events = $this->collector->getEvents();
        $this->assertEquals(1, $events[0]->depth);

        $this->collector->decrementDepth();
        $this->assertEquals(0, $this->collector->getCurrentDepth());
    }

    public function testGetEventsByType(): void
    {
        $this->collector->enable();

        $this->collector->recordPropertyHydration('Test', 'prop1', null, 'v1', 'source');
        $this->collector->recordPropertySkipped('Test', 'prop2', 'locked');
        $this->collector->recordPropertyHydration('Test', 'prop3', null, 'v3', 'source');

        $hydrationEvents = $this->collector->getEventsByType(LineageEvent::TYPE_PROPERTY_HYDRATION);
        $this->assertCount(2, $hydrationEvents);

        $skippedEvents = $this->collector->getEventsByType(LineageEvent::TYPE_PROPERTY_SKIPPED);
        $this->assertCount(1, $skippedEvents);
    }

    public function testGetSummary(): void
    {
        $this->collector->enable();

        $this->collector->recordPropertyHydration('Test', 'prop1', null, 'v1', 'source');
        $this->collector->recordPropertySkipped('Test', 'prop2', 'locked');
        $this->collector->recordPropertySkipped('Test', 'prop3', 'priority');

        $summary = $this->collector->getSummary();

        $this->assertEquals(3, $summary['totalEvents']);
        $this->assertEquals(1, $summary['eventsByType'][LineageEvent::TYPE_PROPERTY_HYDRATION]);
        $this->assertEquals(2, $summary['eventsByType'][LineageEvent::TYPE_PROPERTY_SKIPPED]);
        $this->assertEquals(1, $summary['skippedReasons']['locked']);
        $this->assertEquals(1, $summary['skippedReasons']['priority']);
    }

    public function testClear(): void
    {
        $this->collector->enable();
        $this->collector->recordPropertyHydration('Test', 'prop', null, 'v', 'source');
        $this->collector->incrementDepth();

        $this->assertCount(1, $this->collector->getEvents());
        $this->assertEquals(1, $this->collector->getCurrentDepth());

        $this->collector->clear();

        $this->assertEmpty($this->collector->getEvents());
        $this->assertEquals(0, $this->collector->getCurrentDepth());
    }

    public function testReset(): void
    {
        $this->collector->enable();
        $this->collector->recordPropertyHydration('Test', 'prop', null, 'v', 'source');

        $this->collector->reset();

        $this->assertFalse($this->collector->isEnabled());
        $this->assertEmpty($this->collector->getEvents());
    }
}
