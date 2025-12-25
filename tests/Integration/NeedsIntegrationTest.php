<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Needs;
use Kassko\DataMapper\DataMapperBuilder;
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use PHPUnit\Framework\TestCase;

class NeedsIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testNeedsAttributeLoadsDependenciesInOrder(): void
    {
        // Track loading order
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
            
            public function getData(): array
            {
                $this->loadOrder[] = 'propB';
                return ['propB' => 'valueB'];
            }
        };
        
        $sourceC = new class($loadOrder) {
            public function __construct(private array &$loadOrder) {}
            
            public function getData(string $a, string $b): array
            {
                $this->loadOrder[] = 'propC';
                return ['propC' => "combined-{$a}-{$b}"];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'SourceA' => $sourceA,
            'SourceB' => $sourceB,
            'SourceC' => $sourceC,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object with Needs dependency
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'sourceA', class: 'SourceA', method: 'getData'),
            new MultiPropDataSource(id: 'sourceB', class: 'SourceB', method: 'getData'),
            new MultiPropDataSource(id: 'sourceC', class: 'SourceC', method: 'getData', args: ['#propA', '#propB']),
        ])]
        class {
            use LoadableInternalTrait;
            
            #[DataSourceRef(id: 'sourceA')]
            private ?string $propA = null;
            
            #[DataSourceRef(id: 'sourceB')]
            private ?string $propB = null;
            
            #[Needs(['propA', 'propB'])]
            #[DataSourceRef(id: 'sourceC')]
            private ?string $propC = null;
            
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
            
            public function getPropC(): ?string
            {
                $this->loadProperty('propC');
                return $this->propC;
            }
        };
        
        // Access propC (which should load propA and propB first)
        $propC = $testObject->getPropC();
        
        // Verify loading order: propA, then propB, then propC
        $this->assertSame(['propA', 'propB', 'propC'], $loadOrder);
        
        // Verify values
        $this->assertSame('valueA', $testObject->getPropA());
        $this->assertSame('valueB', $testObject->getPropB());
        $this->assertSame('combined-valueA-valueB', $propC);
    }
    
    public function testNeedsAttributeDoesNotReloadAlreadyLoadedProperties(): void
    {
        // Track loading count
        $loadCount = ['propA' => 0, 'propB' => 0];
        
        // Create data sources that track loading
        $sourceA = new class($loadCount) {
            public function __construct(private array &$loadCount) {}
            
            public function getData(): array
            {
                $this->loadCount['propA']++;
                return ['propA' => 'valueA'];
            }
        };
        
        $sourceB = new class($loadCount) {
            public function __construct(private array &$loadCount) {}
            
            public function getData(): array
            {
                $this->loadCount['propB']++;
                return ['propB' => 'valueB'];
            }
        };
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            'SourceA' => $sourceA,
            'SourceB' => $sourceB,
        ]);
        
        $builder = new DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(id: 'sourceA', class: 'SourceA', method: 'getData'),
            new MultiPropDataSource(id: 'sourceB', class: 'SourceB', method: 'getData'),
        ])]
        class {
            use LoadableInternalTrait;
            
            #[DataSourceRef(id: 'sourceA')]
            private ?string $propA = null;
            
            #[Needs(['propA'])]
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
        
        // Load propA first
        $testObject->getPropA();
        $this->assertSame(1, $loadCount['propA']);
        
        // Load propB (which needs propA, but it's already loaded)
        $testObject->getPropB();
        $this->assertSame(1, $loadCount['propA']); // Should not reload
        $this->assertSame(1, $loadCount['propB']);
    }
}
