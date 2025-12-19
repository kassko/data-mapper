<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Exception\NoValidDataSourceException;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use Kassko\Sample\ChainDataSource;
use Kassko\Sample\UnsuitableSourceException;
use PHPUnit\Framework\TestCase;

#[DataSourcesStore([
    new DataSource(id: 'sourceA', class: ChainDataSource::class, method: 'failingSource'),
    new DataSource(id: 'sourceB', class: ChainDataSource::class, method: 'successfulSource'),
    new DataSource(id: 'sourceC', class: ChainDataSource::class, method: 'unexpectedFailure'),
])]
class DataSourceChainTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testChainFallbackWithSuccess(): void
    {
        new DataMapper();
        
        $object = new #[DataSourcesStore([
            new DataSource(id: 'sourceA', class: ChainDataSource::class, method: 'failingSource'),
            new DataSource(id: 'sourceB', class: ChainDataSource::class, method: 'successfulSource'),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(chain: ['sourceA', 'sourceB'], exception: UnsuitableSourceException::class)]
            private ?string $data = null;
            
            public function getData(): ?string
            {
                $this->loadProperty('data');
                return $this->data;
            }
        };
        
        $this->assertEquals('data-from-B', $object->getData());
    }
    
    public function testChainThrowsExceptionWhenAllFail(): void
    {
        new DataMapper();
        
        $object = new #[DataSourcesStore([
            new DataSource(id: 'sourceA', class: ChainDataSource::class, method: 'failingSource'),
            new DataSource(id: 'sourceB', class: ChainDataSource::class, method: 'failingSource'),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(chain: ['sourceA', 'sourceB'], exception: UnsuitableSourceException::class)]
            private ?string $data = null;
            
            public function getData(): ?string
            {
                $this->loadProperty('data');
                return $this->data;
            }
        };
        
        $this->expectException(NoValidDataSourceException::class);
        $object->getData();
    }
    
    public function testChainRethrowsUnexpectedException(): void
    {
        new DataMapper();
        
        $object = new #[DataSourcesStore([
            new DataSource(id: 'sourceA', class: ChainDataSource::class, method: 'unexpectedFailure'),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(chain: ['sourceA'], exception: UnsuitableSourceException::class)]
            private ?string $data = null;
            
            public function getData(): ?string
            {
                $this->loadProperty('data');
                return $this->data;
            }
        };
        
        $this->expectException(\LogicException::class);
        $object->getData();
    }
}
