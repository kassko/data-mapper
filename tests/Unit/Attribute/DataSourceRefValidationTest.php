<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Unit\Attribute;

use Kassko\DataMapper\Attribute\DataSourceRef;
use PHPUnit\Framework\TestCase;

class DataSourceRefValidationTest extends TestCase
{
    public function testIdOnlyIsValid(): void
    {
        $ref = new DataSourceRef(id: 'sourceA');
        $this->assertEquals('sourceA', $ref->id);
        $this->assertNull($ref->chain);
        $this->assertNull($ref->providers);
    }
    
    public function testChainWithExceptionIsValid(): void
    {
        $ref = new DataSourceRef(
            chain: ['sourceA', 'sourceB'],
            exceptionOnNoValidDataSource: 'SomeException'
        );
        $this->assertEquals(['sourceA', 'sourceB'], $ref->chain);
        $this->assertEquals('SomeException', $ref->exceptionOnNoValidDataSource);
        $this->assertNull($ref->id);
        $this->assertNull($ref->providers);
    }
    
    public function testProvidersOnlyIsValid(): void
    {
        $ref = new DataSourceRef(providers: ['providerA', 'providerB']);
        $this->assertEquals(['providerA', 'providerB'], $ref->providers);
        $this->assertNull($ref->id);
        $this->assertNull($ref->chain);
    }
    
    public function testNoParametersThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef requires one of: id, chain, or providers');
        
        new DataSourceRef();
    }
    
    public function testIdAndChainAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, chain, and providers are mutually exclusive');
        
        new DataSourceRef(
            id: 'sourceA',
            chain: ['sourceB'],
            exceptionOnNoValidDataSource: 'SomeException'
        );
    }
    
    public function testIdAndProvidersAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, chain, and providers are mutually exclusive');
        
        new DataSourceRef(
            id: 'sourceA',
            providers: ['providerB']
        );
    }
    
    public function testChainAndProvidersAreMutuallyExclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: id, chain, and providers are mutually exclusive');
        
        new DataSourceRef(
            chain: ['sourceA'],
            providers: ['providerB'],
            exceptionOnNoValidDataSource: 'SomeException'
        );
    }
    
    public function testChainWithoutExceptionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: chain and exceptionOnNoValidDataSource must both be present or both absent');
        
        new DataSourceRef(chain: ['sourceA', 'sourceB']);
    }
    
    public function testExceptionWithoutChainThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DataSourceRef: chain and exceptionOnNoValidDataSource must both be present or both absent');
        
        new DataSourceRef(exceptionOnNoValidDataSource: 'SomeException');
    }
}
