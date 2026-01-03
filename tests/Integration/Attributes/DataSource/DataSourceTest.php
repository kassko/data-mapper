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

namespace Kassko\DataMapper\Tests\Integration\Attributes\DataSource;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\DataSource\Post;
use Kassko\Sample\DataSource\User;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for DataSource attribute.
 * 
 * DataSource is an alias for SinglePropDataSource. It defines a data source
 * that hydrates a single property via lazy loading. Each property has its 
 * own data source method.
 * 
 * Note: This test mirrors SinglePropDataSourceTest to verify that DataSource
 * (as a distinct attribute class) works identically to SinglePropDataSource.
 */
class DataSourceTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    /**
     * Test basic DataSource loading for a single property.
     */
    public function testSinglePropertyLoading(): void
    {
        new DataMapper(new ServiceResolver());
        
        $user = new User(1);
        
        $username = $user->getUsername();
        
        $this->assertEquals('admin', $username);
    }

    /**
     * Test that different properties are loaded independently.
     */
    public function testMultiplePropertiesLoadedIndependently(): void
    {
        new DataMapper(new ServiceResolver());
        
        $user = new User(1);
        
        // Load username first
        $username = $user->getUsername();
        $this->assertEquals('admin', $username);
        
        // Load email separately
        $email = $user->getEmail();
        $this->assertEquals('admin@example.com', $email);
        
        // Load role separately
        $role = $user->getRole();
        $this->assertEquals('ROLE_ADMIN', $role);
    }

    /**
     * Test DataSource with different entity IDs.
     */
    public function testDifferentEntityIds(): void
    {
        new DataMapper(new ServiceResolver());
        
        $admin = new User(1);
        $editor = new User(2);
        $guest = new User(3);
        
        $this->assertEquals('admin', $admin->getUsername());
        $this->assertEquals('editor', $editor->getUsername());
        $this->assertEquals('guest', $guest->getUsername());
        
        $this->assertEquals('ROLE_ADMIN', $admin->getRole());
        $this->assertEquals('ROLE_EDITOR', $editor->getRole());
        $this->assertEquals('ROLE_USER', $guest->getRole());
    }

    /**
     * Test DataSource with id parameter for referencing.
     */
    public function testDataSourceWithId(): void
    {
        new DataMapper(new ServiceResolver());
        
        $post = new Post(1);
        
        $title = $post->getTitle();
        $body = $post->getBody();
        $authorName = $post->getAuthorName();
        
        $this->assertEquals('Welcome to our blog', $title);
        $this->assertEquals('This is the first post on our new blog...', $body);
        $this->assertEquals('Admin', $authorName);
    }

    /**
     * Test that unknown ID returns null values.
     */
    public function testUnknownIdReturnsNull(): void
    {
        new DataMapper(new ServiceResolver());
        
        $user = new User(999);
        
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getRole());
    }

    /**
     * Test that properties can be loaded multiple times (cached).
     */
    public function testPropertyLoadingIsCached(): void
    {
        new DataMapper(new ServiceResolver());
        
        $user = new User(1);
        
        // First call loads the property
        $username1 = $user->getUsername();
        
        // Second call should return the same cached value
        $username2 = $user->getUsername();
        
        $this->assertEquals($username1, $username2);
        $this->assertEquals('admin', $username1);
    }

    /**
     * Test multiple posts with different IDs.
     */
    public function testMultiplePosts(): void
    {
        new DataMapper(new ServiceResolver());
        
        $post1 = new Post(1);
        $post2 = new Post(2);
        $post3 = new Post(3);
        
        $this->assertEquals('Welcome to our blog', $post1->getTitle());
        $this->assertEquals('PHP 8 Features', $post2->getTitle());
        $this->assertEquals('Testing Guide', $post3->getTitle());
        
        $this->assertEquals('Admin', $post1->getAuthorName());
        $this->assertEquals('Developer', $post2->getAuthorName());
        $this->assertEquals('QA Engineer', $post3->getAuthorName());
    }

    /**
     * Test that ID is accessible before loading properties.
     */
    public function testIdAccessibleBeforeLoading(): void
    {
        new DataMapper(new ServiceResolver());
        
        $user = new User(42);
        
        // ID should be accessible immediately
        $this->assertEquals(42, $user->getId());
        
        // Property loading should work (even if null for unknown ID)
        $this->assertNull($user->getUsername());
    }
}
