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
use Kassko\DataMapper\Enum\SensitiveLevel;
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

    public function testSensitiveLevelHide(): void
    {
        $collector = new DataLineageCollector([
            'password' => SensitiveLevel::HIDE,
        ]);
        $collector->enable();

        $collector->recordPropertyHydration('Test', 'password', null, 'secret123', 'source');

        $events = $collector->getEvents();
        $this->assertCount(1, $events);
        
        $array = $events[0]->toArray();
        $this->assertEquals('[SENSITIVE]', $array['finalValue']);
    }

    public function testSensitiveLevelMask(): void
    {
        $collector = new DataLineageCollector([
            'ssn' => SensitiveLevel::MASK,
        ]);
        $collector->enable();

        $collector->recordPropertyHydration('Test', 'ssn', null, '123-45-6789', 'source');

        $events = $collector->getEvents();
        $array = $events[0]->toArray();
        
        // Should mask the middle
        $this->assertStringStartsWith('12', $array['finalValue']);
        $this->assertStringEndsWith('89', $array['finalValue']);
        $this->assertStringContainsString('*', $array['finalValue']);
    }

    public function testSensitiveLevelTypeOnly(): void
    {
        $collector = new DataLineageCollector([
            'data' => SensitiveLevel::TYPE_ONLY,
        ]);
        $collector->enable();

        $collector->recordPropertyHydration('Test', 'data', null, 'some value', 'source');

        $events = $collector->getEvents();
        $array = $events[0]->toArray();
        
        $this->assertStringContainsString('[string', $array['finalValue']);
    }

    public function testSensitivePatternMatching(): void
    {
        $collector = new DataLineageCollector([
            '*password*' => SensitiveLevel::HIDE,
        ]);
        $collector->enable();

        $collector->recordPropertyHydration('Test', 'user_password_hash', null, 'hashvalue', 'source');

        $events = $collector->getEvents();
        $array = $events[0]->toArray();
        
        $this->assertEquals('[SENSITIVE]', $array['finalValue']);
    }

    public function testDatetimeIsRecorded(): void
    {
        $this->collector->enable();

        $this->collector->recordPropertyHydration('Test', 'prop', null, 'value', 'source');

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\DateTimeImmutable::class, $events[0]->datetime);
        
        $array = $events[0]->toArray();
        $this->assertArrayHasKey('datetime', $array);
        $this->assertNotNull($array['datetime']);
    }

    public function testFlowTracking(): void
    {
        $this->collector->enable();

        // Start a flow
        $flowId = $this->collector->startFlow('TestClass', 'Test hydration');
        $this->assertNotEmpty($flowId);
        $this->assertEquals($flowId, $this->collector->getCurrentFlowId());

        // Record an event - should have the flow ID
        $this->collector->recordPropertyHydration('TestClass', 'prop', null, 'value', 'source');

        // End the flow
        $this->collector->endFlow('completed');

        $events = $this->collector->getEvents();
        
        // Flow start + property hydration + flow end = 3 events
        $this->assertCount(3, $events);
        
        // Check flow start event
        $this->assertEquals(LineageEvent::TYPE_FLOW_START, $events[0]->type);
        $this->assertEquals($flowId, $events[0]->flowId);
        
        // Check hydration event has flow ID
        $this->assertEquals($flowId, $events[1]->flowId);
        
        // Check flow end event
        $this->assertEquals(LineageEvent::TYPE_FLOW_END, $events[2]->type);
        $this->assertEquals($flowId, $events[2]->flowId);
    }

    public function testAddAndRemoveSensitiveKeyAtRuntime(): void
    {
        $this->collector->enable();
        
        // Add a sensitive key at runtime
        $this->collector->addSensitiveKey('secret', SensitiveLevel::HIDE);
        
        $this->collector->recordPropertyHydration('Test', 'secret', null, 'value', 'source');
        
        $events = $this->collector->getEvents();
        $array = $events[0]->toArray();
        $this->assertEquals('[SENSITIVE]', $array['finalValue']);
        
        // Remove the key and verify
        $this->collector->removeSensitiveKey('secret');
        $this->assertArrayNotHasKey('secret', $this->collector->getSensitiveKeys());
    }

    public function testDefaultSensitiveLevel(): void
    {
        $collector = new DataLineageCollector([], SensitiveLevel::TYPE_ONLY);
        $collector->enable();

        $collector->recordPropertyHydration('Test', 'anyProperty', null, 'any value', 'source');

        $events = $collector->getEvents();
        $array = $events[0]->toArray();
        
        $this->assertStringContainsString('[string', $array['finalValue']);
    }

    public function testDataSourceSensitiveKeys(): void
    {
        $this->collector->enable();

        // Record a DataSource call with sensitiveKeys
        $this->collector->recordDataSourceCall(
            'TestClass',
            'UserRepository',
            'findUser',
            ['id' => 123],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret123',
                'apiToken' => 'token-abc-123',
            ],
            'userSource',
            [
                'password' => SensitiveLevel::HIDE,
                '*Token' => SensitiveLevel::MASK,
            ]
        );

        $events = $this->collector->getEvents();
        $this->assertCount(1, $events);
        
        $array = $events[0]->toArray();
        
        // password should be hidden
        $this->assertEquals('[SENSITIVE]', $array['finalValue']['password']);
        
        // apiToken should be masked (pattern match *Token)
        $this->assertStringContainsString('***', $array['finalValue']['apiToken']);
        
        // name and email should be shown as-is
        $this->assertEquals('John Doe', $array['finalValue']['name']);
        $this->assertEquals('john@example.com', $array['finalValue']['email']);
        
        // metadata should indicate hasSensitiveKeys
        $this->assertTrue($array['metadata']['hasSensitiveKeys']);
    }

    public function testDataSourceWithoutSensitiveKeys(): void
    {
        $this->collector->enable();

        $this->collector->recordDataSourceCall(
            'TestClass',
            'Repository',
            'findAll',
            [],
            ['data' => 'value'],
            'source',
            [] // No sensitive keys
        );

        $events = $this->collector->getEvents();
        $array = $events[0]->toArray();
        
        // Data should be shown as-is
        $this->assertEquals(['data' => 'value'], $array['finalValue']);
        
        // metadata should indicate no sensitive keys
        $this->assertFalse($array['metadata']['hasSensitiveKeys']);
    }
}
