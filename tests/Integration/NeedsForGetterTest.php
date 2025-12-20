<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use PHPUnit\Framework\TestCase;

class NeedsForGetterTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testNeedsLoadsPropertiesUsedInGetter(): void
    {
        // Track loading order
        $loadOrder = [];
        
        // Create data sources
        $sourceD = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(): array
            {
                $this->loadOrder[] = 'propD';
                return ['propD' => 100];
            }
        };
        
        $sourceF = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(): array
            {
                $this->loadOrder[] = 'propF';
                return ['propF' => 50];
            }
        };
        
        $sourceC = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(): array
            {
                $this->loadOrder[] = 'propC';
                return ['propC' => 200];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'SourceC' => $sourceC,
            'SourceD' => $sourceD,
            'SourceF' => $sourceF,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object with Needs for getter use case
        $testObject = new 
        #[MultiPropDataSource(id: 'sourceC', class: 'SourceC', method: 'getData')]
        #[MultiPropDataSource(id: 'sourceD', class: 'SourceD', method: 'getData')]
        #[MultiPropDataSource(id: 'sourceF', class: 'SourceF', method: 'getData')]
        class {
            use LoadableTrait;
            
            // propD and propF are NOT in args, but are used in the getter
            // So we need Needs to load them before propC
            #[Needs(['propD', 'propF'])]
            #[DataSourceRef(id: 'sourceC')]
            private ?int $propC = null;
            
            #[DataSourceRef(id: 'sourceD')]
            private ?int $propD = null;
            
            #[DataSourceRef(id: 'sourceF')]
            private ?int $propF = null;
            
            public function getPropC(): ?int
            {
                $this->loadProperty('propC');
                
                // propD and propF are used here in the getter
                // They must be loaded via Needs before propC
                if ($this->propD > $this->propF) {
                    return $this->propC;
                }
                return $this->propC + $this->propF;
            }
        };
        
        // Access propC (which should load propD and propF first via Needs)
        $result = $testObject->getPropC();
        
        // Verify loading order: propD and propF loaded before propC
        $this->assertSame(['propD', 'propF', 'propC'], $loadOrder);
        
        // Verify result (propD=100 > propF=50, so return propC=200)
        $this->assertSame(200, $result);
    }
    
    public function testNeedsVsAutoLoadingDifference(): void
    {
        // This test demonstrates the difference between:
        // - Auto-loading (properties in args)
        // - Needs (properties used elsewhere, like in getter)
        
        $loadOrder = [];
        
        // Create data sources
        $sourceA = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(): array
            {
                $this->loadOrder[] = 'propA';
                return ['propA' => 'A'];
            }
        };
        
        $sourceB = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(string $input): array
            {
                $this->loadOrder[] = 'propB';
                return ['propB' => "B-{$input}"];
            }
        };
        
        $sourceValidator = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(): array
            {
                $this->loadOrder[] = 'validator';
                return ['validator' => true];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'SourceA' => $sourceA,
            'SourceB' => $sourceB,
            'SourceValidator' => $sourceValidator,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object
        $testObject = new 
        #[MultiPropDataSource(id: 'sourceA', class: 'SourceA', method: 'getData')]
        #[MultiPropDataSource(id: 'sourceB', class: 'SourceB', method: 'getData', args: ['#propA'])]
        #[MultiPropDataSource(id: 'sourceValidator', class: 'SourceValidator', method: 'getData')]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'sourceA')]
            private ?string $propA = null;
            
            // propA is in args -> auto-loads (NO Needs needed)
            // validator is NOT in args, but used in getter -> needs Needs
            #[Needs(['validator'])]
            #[DataSourceRef(id: 'sourceB')]
            private ?string $propB = null;
            
            #[DataSourceRef(id: 'sourceValidator')]
            private ?bool $validator = null;
            
            public function getPropB(): ?string
            {
                $this->loadProperty('propB');
                
                // validator is used here - must be loaded via Needs
                if (!$this->validator) {
                    throw new \RuntimeException('Validation failed');
                }
                
                return $this->propB;
            }
        };
        
        // Access propB
        $result = $testObject->getPropB();
        
        // Verify loading order:
        // 1. validator (from Needs)
        // 2. propA (auto-loaded because in args)
        // 3. propB
        $this->assertSame(['validator', 'propA', 'propB'], $loadOrder);
        $this->assertSame('B-A', $result);
    }
}
