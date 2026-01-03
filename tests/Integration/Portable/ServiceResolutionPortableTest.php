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

namespace Kassko\DataMapper\Tests\Integration\Portable;

use Kassko\DataMapper\Tests\TestHelpers\PortableIntegrationTestCase;
use Kassko\Sample\Portable\User;
use Kassko\Sample\Portable\UserDataSource;

/**
 * Portable integration tests for service resolution functionality.
 * 
 * These tests verify that services (DataSources) are correctly resolved
 * in both native PHP and Symfony Bundle contexts.
 */
class ServiceResolutionPortableTest extends PortableIntegrationTestCase
{
    public function testServiceResolutionWithServiceId(): void
    {
        // Register the service with the @service.id pattern
        $this->getDataMapper([
            'user.data_source' => new UserDataSource(),
        ]);
        
        $user = new User(1);
        
        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals('ROLE_ADMIN', $user->getRole());
    }

    public function testServiceResolutionWithDifferentUsers(): void
    {
        $this->getDataMapper([
            'user.data_source' => new UserDataSource(),
        ]);
        
        $admin = new User(1);
        $editor = new User(2);
        $guest = new User(3);
        
        $this->assertEquals('admin', $admin->getUsername());
        $this->assertEquals('ROLE_ADMIN', $admin->getRole());
        
        $this->assertEquals('editor', $editor->getUsername());
        $this->assertEquals('ROLE_EDITOR', $editor->getRole());
        
        $this->assertEquals('guest', $guest->getUsername());
        $this->assertEquals('ROLE_USER', $guest->getRole());
    }

    public function testServiceResolutionWithSymfonyContainer(): void
    {
        // This test only runs in Symfony context
        $this->requiresSymfonyContext('testing Symfony container integration');
        
        // First get a DataMapper to ensure the container is booted
        $this->getDataMapper();
        
        // In Symfony context, the container is available
        $container = $this->getContainer();
        
        $this->assertNotNull($container);
    }
}
