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

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use PHPUnit\Framework\TestCase;

class AutoLoadArgsTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testPropertyInArgsIsAutoLoadedWithoutNeeds(): void
    {
        // Track loading order to verify propA is loaded before propB
        $loadOrder = [];
        
        // Create data sources
        $sourceA = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(): array
            {
                $this->loadOrder[] = 'propA';
                return ['propA' => 'valueA'];
            }
        };
        
        $sourceB = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            // This method takes propA as argument
            public function getData(string $propAValue): array
            {
                $this->loadOrder[] = 'propB';
                return ['propB' => "derived-from-{$propAValue}"];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'SourceA' => $sourceA,
            'SourceB' => $sourceB,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();
        
        // Create test object WITHOUT Needs attribute
        // propA should auto-load because it's used in args of propB's DataSource
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'sourceA', class: 'SourceA', method: 'getData'),
            new MultiPropDataSource(id: 'sourceB', class: 'SourceB', method: 'getData', args: ['#propA']),
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'sourceA')]
            private ?string $propA = null;
            
            // NO Needs attribute - propA should auto-load because it's in args
            #[DataSourceRef(id: 'sourceB')]
            private ?string $propB = null;
            
            public function getPropA(): ?string
            {
                $this->loadProperty('propA');
                return $this->propA;
            }
            
            public function getPropB(): ?string
            {
                $this->loadProperty('propB');
                return $this->propB;
            }
        };
        
        // Access propB (which should auto-load propA because it's in args)
        $propB = $testObject->getPropB();
        
        // Verify loading order: propA auto-loaded first, then propB
        $this->assertSame(['propA', 'propB'], $loadOrder);
        
        // Verify values
        $this->assertSame('valueA', $testObject->getPropA());
        $this->assertSame('derived-from-valueA', $propB);
    }
    
    public function testMultiplePropertiesInArgsAutoLoad(): void
    {
        // Track loading
        $loadCount = ['propA' => 0, 'propB' => 0, 'propC' => 0];
        
        // Create data sources
        $sourceA = new class($loadCount) {
            public function __construct(private array &$loadCount) {}
            
            public function getData(): array
            {
                $this->loadCount['propA']++;
                return ['propA' => 10];
            }
        };
        
        $sourceB = new class($loadCount) {
            public function __construct(private array &$loadCount) {}
            
            public function getData(): array
            {
                $this->loadCount['propB']++;
                return ['propB' => 20];
            }
        };
        
        $sourceC = new class($loadCount) {
            public function __construct(private array &$loadCount) {}
            
            // This method takes both propA and propB as arguments
            public function calculate(int $a, int $b): array
            {
                $this->loadCount['propC']++;
                return ['propC' => $a + $b];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'SourceA' => $sourceA,
            'SourceB' => $sourceB,
            'SourceC' => $sourceC,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addServiceLocator($serviceLocator);
        $builder->build();
        
        // Create test object WITHOUT Needs attribute
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'sourceA', class: 'SourceA', method: 'getData'),
            new MultiPropDataSource(id: 'sourceB', class: 'SourceB', method: 'getData'),
            new MultiPropDataSource(id: 'sourceC', class: 'SourceC', method: 'calculate', args: ['#propA', '#propB']),
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'sourceA')]
            private ?int $propA = null;
            
            #[DataSourceRef(id: 'sourceB')]
            private ?int $propB = null;
            
            // NO Needs attribute - both propA and propB should auto-load
            #[DataSourceRef(id: 'sourceC')]
            private ?int $propC = null;
            
            public function getPropC(): ?int
            {
                $this->loadProperty('propC');
                return $this->propC;
            }
        };
        
        // Access propC (which should auto-load both propA and propB)
        $propC = $testObject->getPropC();
        
        // Verify each property loaded exactly once
        $this->assertSame(1, $loadCount['propA']);
        $this->assertSame(1, $loadCount['propB']);
        $this->assertSame(1, $loadCount['propC']);
        
        // Verify result
        $this->assertSame(30, $propC);
    }
}
