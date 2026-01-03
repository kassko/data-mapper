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
use Kassko\DataMapper\Attribute\PropertySettingHook;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\HookExternalService\EmailDataSource;
use Kassko\Sample\HookExternalService\NameDataSource;
use Kassko\Sample\HookExternalService\StatusDataSource;
use Kassko\Sample\HookExternalService\TestLogService;
use Kassko\Sample\HookExternalService\TestValidationService;
use PHPUnit\Framework\TestCase;

class HookExternalServiceTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testHookWithExternalServiceClass(): void
    {
        // Create instances
        $validationService = new TestValidationService();
        $dataSource = new EmailDataSource();
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            TestValidationService::class => $validationService,
            EmailDataSource::class => $dataSource,
        ]);
        
        // Create DataMapper with service locator via builder
        $builder = new \Kassko\DataMapper\DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create a test object with hook to external service
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(
                id: 'emailSource',
                class: EmailDataSource::class,
                method: 'getEmailData',
            )
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'emailSource')]
            #[PropertySettingHook(
                after_set_property: 'validateEmail',
                class: TestValidationService::class,
                args: ['##object', '#email']
            )]
            private ?string $email = null;
            
            public function getEmail(): ?string
            {
                $this->loadProperty('email');
                return $this->email;
            }
        };
        
        // Access the property (this will trigger lazy loading and the hook)
        $email = $testObject->getEmail();
        
        // Verify the external service was called
        $this->assertSame(['test@example.com'], $validationService->validatedEmails);
        $this->assertSame('test@example.com', $email);
    }
    
    public function testHookWithExternalServiceAndMultipleArgs(): void
    {
        // Create instances
        $logService = new TestLogService();
        $dataSource = new StatusDataSource();
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            TestLogService::class => $logService,
            StatusDataSource::class => $dataSource,
        ]);
        
        // Create DataMapper via builder
        $builder = new \Kassko\DataMapper\DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create test object with hook to external logging service
        $testObject = new 
        #[DataSourcesStore([
            new MultiPropDataSource(
                id: 'statusSource',
                class: StatusDataSource::class,
                method: 'getStatusData',
            )
        ])]
        class {
            use LoadableTrait;
            
            #[DataSourceRef(id: 'statusSource')]
            #[PropertySettingHook(
                after_set_property: 'logChange',
                class: TestLogService::class,
                args: ['##object', 'status', 'draft', '#status']
            )]
            private string $status = 'draft';
            
            public function getStatus(): string
            {
                $this->loadProperty('status');
                return $this->status;
            }
        };
        
        // Access property (this will trigger lazy loading and the hook)
        $status = $testObject->getStatus();
        
        // Verify the logging service was called
        $this->assertCount(1, $logService->logs);
        $this->assertSame('status', $logService->logs[0]['property']);
        $this->assertSame('draft', $logService->logs[0]['old']);
        $this->assertSame('published', $logService->logs[0]['new']);
        $this->assertSame('published', $status);
    }
    
    public function testHookWithoutExternalServiceUsesDataObject(): void
    {
        // Create data source
        $dataSource = new NameDataSource();
        
        // Setup service locator
        $serviceLocator = new ArrayServiceLocator([
            NameDataSource::class => $dataSource,
        ]);
        
        // Create DataMapper via builder
        $builder = new \Kassko\DataMapper\DataMapperBuilder();
        $builder->addLocator($serviceLocator);
        $builder->build();
        
        // Create a test object with hook on itself (no class parameter)
        $testObject = new
        #[DataSourcesStore([
            new MultiPropDataSource(
                id: 'nameSource',
                class: NameDataSource::class,
                method: 'getNameData',
            )
        ])]
        class {
            use LoadableTrait;
            
            public bool $validated = false;
            
            #[DataSourceRef(id: 'nameSource')]
            #[PropertySettingHook(
                after_set_property: 'validate',
                args: ['#name']
            )]
            private ?string $name = null;
            
            public function getName(): ?string
            {
                $this->loadProperty('name');
                return $this->name;
            }
            
            public function validate(?string $name): void
            {
                $this->validated = ($name !== null && strlen($name) > 0);
            }
        };
        
        // Access property (this will trigger lazy loading and the hook)
        $name = $testObject->getName();
        
        // Verify the method was called on the object itself
        $this->assertTrue($testObject->validated);
        $this->assertSame('John', $name);
    }
}
