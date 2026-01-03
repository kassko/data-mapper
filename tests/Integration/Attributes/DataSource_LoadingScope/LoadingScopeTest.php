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

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\Sample\DataSourceLoadingScope\PersonFullDataSource;
use PHPUnit\Framework\TestCase;

class LoadingScopeTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testScopeProperty(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $object = new #[DataSourcesStore([
            new MultiPropDataSource(
                id: 'personData',
                class: PersonFullDataSource::class,
                method: 'getFullData',
                args: [1],
                loadingScope: MultiPropDataSource::SCOPE_ONLY_PROPS,
                loadingScopeProps: ['firstName']
            ),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(name: 'first_name')]
            private ?string $firstName = null;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(name: 'last_name')]
            private ?string $lastName = null;
            
            #[DataSourceRef(id: 'personData')]
            private ?string $email = null;
            
            public function getFirstName(): ?string
            {
                $this->loadProperty('firstName');
                return $this->firstName;
            }
            
            public function getLastName(): ?string
            {
                $this->loadProperty('lastName');
                return $this->lastName;
            }
            
            public function getEmail(): ?string
            {
                $this->loadProperty('email');
                return $this->email;
            }
        };
        
        // Load firstName - only firstName should be loaded due to SCOPE_ONLY_PROPS
        $this->assertEquals('John', $object->getFirstName());
        
        // lastName and email should not be auto-loaded
        $this->assertNull($object->getLastName());
        $this->assertNull($object->getEmail());
    }
    
    public function testScopeOnlyKeys(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $object = new #[DataSourcesStore([
            new MultiPropDataSource(
                id: 'personData',
                class: PersonFullDataSource::class,
                method: 'getFullData',
                args: [2],
                loadingScope: MultiPropDataSource::SCOPE_ONLY_KEYS,
                loadingScopeKeys: ['first_name', 'last_name']
            ),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(name: 'first_name')]
            private ?string $firstName = null;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(name: 'last_name')]
            private ?string $lastName = null;
            
            #[DataSourceRef(id: 'personData')]
            private ?string $email = null;
            
            #[DataSourceRef(id: 'personData')]
            private ?string $phone = null;
            
            public function getFirstName(): ?string
            {
                $this->loadProperty('firstName');
                return $this->firstName;
            }
            
            public function getLastName(): ?string
            {
                $this->loadProperty('lastName');
                return $this->lastName;
            }
            
            public function getEmail(): ?string
            {
                $this->loadProperty('email');
                return $this->email;
            }
            
            public function getPhone(): ?string
            {
                $this->loadProperty('phone');
                return $this->phone;
            }
        };
        
        // Trigger loading - should only load firstName and lastName
        $this->assertEquals('Jane', $object->getFirstName());
        $this->assertEquals('Smith', $object->getLastName());
        
        // email and phone should not be loaded
        $this->assertNull($object->getEmail());
        $this->assertNull($object->getPhone());
    }
    
    public function testScopeExceptKeys(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());
        
        $object = new #[DataSourcesStore([
            new MultiPropDataSource(
                id: 'personData',
                class: PersonFullDataSource::class,
                method: 'getFullData',
                args: [1],
                loadingScope: MultiPropDataSource::SCOPE_EXCEPT_KEYS,
                loadingScopeKeys: ['phone']
            ),
        ])] class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(name: 'first_name')]
            private ?string $firstName = null;
            
            #[DataSourceRef(id: 'personData')]
            #[Property(name: 'last_name')]
            private ?string $lastName = null;
            
            #[DataSourceRef(id: 'personData')]
            private ?string $email = null;
            
            #[DataSourceRef(id: 'personData')]
            private ?string $phone = null;
            
            public function getFirstName(): ?string
            {
                $this->loadProperty('firstName');
                return $this->firstName;
            }
            
            public function getLastName(): ?string
            {
                $this->loadProperty('lastName');
                return $this->lastName;
            }
            
            public function getEmail(): ?string
            {
                $this->loadProperty('email');
                return $this->email;
            }
            
            public function getPhone(): ?string
            {
                $this->loadProperty('phone');
                return $this->phone;
            }
        };
        
        // Trigger loading - should load everything except phone
        $this->assertEquals('John', $object->getFirstName());
        $this->assertEquals('Doe', $object->getLastName());
        $this->assertEquals('john@example.com', $object->getEmail());
        
        // phone should not be loaded
        $this->assertNull($object->getPhone());
    }
}
