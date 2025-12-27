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

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;
use Kassko\DataMapper\Registry\ContextRegistry;
use Kassko\DataMapper\Registry\LoaderRegistry;
use PHPUnit\Framework\TestCase;

class DataSourceRefCandidatesTest extends TestCase
{
    protected function setUp(): void
    {
        ContextRegistry::clear();
    }

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
        ContextRegistry::clear();
    }

    public function testCandidatesSelectsFirstMatchingSource(): void
    {
        $newFeatureSource = new class {
            public function getData(): array {
                return ['name' => 'from-new-feature'];
            }
        };

        $oldFeatureSource = new class {
            public function getData(): array {
                return ['name' => 'from-old-feature'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'NewFeatureSource' => $newFeatureSource,
            'OldFeatureSource' => $oldFeatureSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $dataMapper = $builder->build();

        // Set context so new feature is enabled
        $dataMapper->addToContext('new_feature_enabled', true);

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'newFeatureSource', class: 'NewFeatureSource', method: 'getData'),
            new MultiPropDataSource(id: 'oldFeatureSource', class: 'OldFeatureSource', method: 'getData'),
        ])]
        class {
            use LoadableInternalTrait;

            #[DataSourceRef(candidates: [
                ['id' => 'newFeatureSource', 'discriminator' => "expr(context('new_feature_enabled'))"],
                ['id' => 'oldFeatureSource', 'discriminator' => 'expr(true)'],
            ])]
            private ?string $name = null;

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $result = $testObject->getName();
        $this->assertSame('from-new-feature', $result);
    }

    public function testCandidatesFallsBackToSecondIfFirstDoesNotMatch(): void
    {
        $newFeatureSource = new class {
            public function getData(): array {
                return ['name' => 'from-new-feature'];
            }
        };

        $oldFeatureSource = new class {
            public function getData(): array {
                return ['name' => 'from-old-feature'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'NewFeatureSource' => $newFeatureSource,
            'OldFeatureSource' => $oldFeatureSource,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $dataMapper = $builder->build();

        // Set context so new feature is DISABLED
        $dataMapper->addToContext('new_feature_enabled', false);

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'newFeatureSource', class: 'NewFeatureSource', method: 'getData'),
            new MultiPropDataSource(id: 'oldFeatureSource', class: 'OldFeatureSource', method: 'getData'),
        ])]
        class {
            use LoadableInternalTrait;

            #[DataSourceRef(candidates: [
                ['id' => 'newFeatureSource', 'discriminator' => "expr(context('new_feature_enabled'))"],
                ['id' => 'oldFeatureSource', 'discriminator' => 'expr(true)'],
            ])]
            private ?string $name = null;

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $result = $testObject->getName();
        $this->assertSame('from-old-feature', $result);
    }

    public function testCandidatePriorityOverridesBasePriority(): void
    {
        $source = new class {
            public function getData(): array {
                return ['name' => 'from-source'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $source,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $dataMapper = $builder->build();
        $dataMapper->enableLineageCollection();
        $dataMapper->addToContext('use_high_priority', true);

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'testSource', class: 'TestSource', method: 'getData'),
        ])]
        class {
            use LoadableInternalTrait;

            #[DataSourceRef(
                candidates: [
                    ['id' => 'testSource', 'discriminator' => "expr(context('use_high_priority'))", 'priority' => 50],
                ],
                priority: 10
            )]
            private ?string $name = null;

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $result = $testObject->getName();
        $this->assertSame('from-source', $result);

        // Verify lineage recorded the candidate resolution with correct priorities
        $collector = $dataMapper->getLineageCollector();
        $events = $collector->getEventsByType('candidate_resolution');
        $this->assertCount(1, $events);
        
        $event = reset($events);
        $this->assertEquals(10, $event->metadata['basePriority']);
        $this->assertEquals(50, $event->metadata['effectivePriority']);
        $this->assertEquals(50, $event->metadata['electedCandidatePriority']);
    }

    public function testNoCandidateMatchedSkipsHydration(): void
    {
        $source = new class {
            public function getData(): array {
                return ['name' => 'from-source'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $source,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $dataMapper = $builder->build();
        $dataMapper->enableLineageCollection();
        $dataMapper->addToContext('never_matches', false);

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'testSource', class: 'TestSource', method: 'getData'),
        ])]
        class {
            use LoadableInternalTrait;

            #[DataSourceRef(candidates: [
                ['id' => 'testSource', 'discriminator' => "expr(context('never_matches'))"],
            ])]
            private ?string $name = 'default-value';

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $result = $testObject->getName();
        // Property should keep its default value since no candidate matched
        $this->assertSame('default-value', $result);

        // Verify lineage recorded the skip
        $collector = $dataMapper->getLineageCollector();
        $skipEvents = $collector->getEventsByType('property_skipped');
        $hasNoMatchSkip = false;
        foreach ($skipEvents as $event) {
            if ($event->reason === 'no_candidate_matched') {
                $hasNoMatchSkip = true;
                break;
            }
        }
        $this->assertTrue($hasNoMatchSkip, 'Expected a property_skipped event with reason no_candidate_matched');
    }

    public function testCandidateResolutionIsRecordedInLineage(): void
    {
        $source = new class {
            public function getData(): array {
                return ['name' => 'from-source'];
            }
        };

        $serviceLocator = new ArrayServiceLocator([
            'TestSource' => $source,
        ]);

        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $dataMapper = $builder->build();
        $dataMapper->enableLineageCollection();
        $dataMapper->addToContext('feature_flag', true);

        $testObject = new #[DataSourcesStore([
            new MultiPropDataSource(id: 'sourceA', class: 'TestSource', method: 'getData'),
            new MultiPropDataSource(id: 'sourceB', class: 'TestSource', method: 'getData'),
        ])]
        class {
            use LoadableInternalTrait;

            #[DataSourceRef(candidates: [
                ['id' => 'sourceA', 'discriminator' => "expr(context('feature_flag'))"],
                ['id' => 'sourceB', 'discriminator' => 'expr(true)'],
            ])]
            private ?string $name = null;

            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
        };

        $testObject->getName();

        $collector = $dataMapper->getLineageCollector();
        $events = $collector->getEventsByType('candidate_resolution');
        
        $this->assertCount(1, $events);
        $event = reset($events);
        
        // Verify the event structure
        $this->assertEquals('name', $event->propertyName);
        $this->assertEquals('sourceA', $event->source); // elected candidate id
        $this->assertEquals('candidate_elected', $event->reason);
        $this->assertCount(2, $event->originalValue); // all candidates
        $this->assertEquals('sourceA', $event->finalValue['id']); // elected candidate
    }
}
