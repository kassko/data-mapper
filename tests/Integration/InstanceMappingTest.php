<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
use PHPUnit\Framework\TestCase;

class AddressSimple
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $country = null;
    
    public function getStreet(): ?string
    {
        return $this->street;
    }
    
    public function getCity(): ?string
    {
        return $this->city;
    }
    
    public function getCountry(): ?string
    {
        return $this->country;
    }
}

class PersonDataSourceForMapping
{
    public function getData(): array
    {
        return [
            'first_name' => 'Dany',
            'billing_street' => '01 Lloyd Road',
            'billing_city' => 'South Siennaborough',
            'billing_country' => 'Tonga',
            'delivery_street' => '12 Lloyd Road',
            'delivery_city' => 'North Siennaborough',
            'delivery_country' => 'Tonga',
        ];
    }
}

class InstanceMappingTest extends TestCase
{
    protected function tearDown(): void
    {
        LazyLoaderRegistry::clear();
    }

    public function testInstanceMappingWithDifferentPrefixes(): void
    {
        new DataMapper();
        
        $person = new #[MultiPropDataSource(
            id: 'personData',
            class: PersonDataSourceForMapping::class,
            method: 'getData'
        )] class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(
                class: AddressSimple::class,
                mapping: ['billing_street' => 'street', 'billing_city' => 'city', 'billing_country' => 'country']
            )]
            private ?AddressSimple $billingAddress = null;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(
                class: AddressSimple::class,
                mapping: ['delivery_street' => 'street', 'delivery_city' => 'city', 'delivery_country' => 'country']
            )]
            private ?AddressSimple $deliveryAddress = null;
            
            public function getBillingAddress(): ?AddressSimple
            {
                $this->loadProperty('billingAddress');
                return $this->billingAddress;
            }
            
            public function getDeliveryAddress(): ?AddressSimple
            {
                $this->loadProperty('deliveryAddress');
                return $this->deliveryAddress;
            }
        };
        
        // Verify billing address
        $billing = $person->getBillingAddress();
        $this->assertInstanceOf(AddressSimple::class, $billing);
        $this->assertEquals('01 Lloyd Road', $billing->getStreet());
        $this->assertEquals('South Siennaborough', $billing->getCity());
        $this->assertEquals('Tonga', $billing->getCountry());
        
        // Verify delivery address
        $delivery = $person->getDeliveryAddress();
        $this->assertInstanceOf(AddressSimple::class, $delivery);
        $this->assertEquals('12 Lloyd Road', $delivery->getStreet());
        $this->assertEquals('North Siennaborough', $delivery->getCity());
        $this->assertEquals('Tonga', $delivery->getCountry());
    }
    
    public function testMappingRequiresClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property: mapping can only be set when class is also specified');
        
        new Property(
            mapping: ['source' => 'target']
        );
    }
    
    public function testMappingWithClass(): void
    {
        // Should not throw exception
        $property = new Property(
            class: AddressSimple::class,
            mapping: ['source' => 'target']
        );
        
        $this->assertEquals(AddressSimple::class, $property->class);
        $this->assertEquals(['source' => 'target'], $property->mapping);
    }
}
