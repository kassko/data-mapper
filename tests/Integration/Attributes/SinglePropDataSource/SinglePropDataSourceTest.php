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

namespace Kassko\DataMapper\Tests\Integration\Attributes\SinglePropDataSource;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\Sample\SinglePropDataSource\Article;
use Kassko\Sample\SinglePropDataSource\Product;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for SinglePropDataSource attribute.
 * 
 * SinglePropDataSource defines a data source that hydrates a single property
 * via lazy loading. Each property has its own data source method.
 */
class SinglePropDataSourceTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    /**
     * Test basic SinglePropDataSource loading for a single property.
     */
    public function testSinglePropertyLoading(): void
    {
        new DataMapper(new ServiceResolver());
        
        $product = new Product(1);
        
        $name = $product->getName();
        
        $this->assertEquals('Laptop', $name);
    }

    /**
     * Test that different properties are loaded independently.
     */
    public function testMultiplePropertiesLoadedIndependently(): void
    {
        new DataMapper(new ServiceResolver());
        
        $product = new Product(1);
        
        // Load name first
        $name = $product->getName();
        $this->assertEquals('Laptop', $name);
        
        // Load price separately
        $price = $product->getPrice();
        $this->assertEquals(999.99, $price);
        
        // Load description separately
        $description = $product->getDescription();
        $this->assertEquals('High performance laptop', $description);
    }

    /**
     * Test SinglePropDataSource with different entity IDs.
     */
    public function testDifferentEntityIds(): void
    {
        new DataMapper(new ServiceResolver());
        
        $laptop = new Product(1);
        $mouse = new Product(2);
        $keyboard = new Product(3);
        
        $this->assertEquals('Laptop', $laptop->getName());
        $this->assertEquals('Mouse', $mouse->getName());
        $this->assertEquals('Keyboard', $keyboard->getName());
        
        $this->assertEquals(999.99, $laptop->getPrice());
        $this->assertEquals(29.99, $mouse->getPrice());
        $this->assertEquals(79.99, $keyboard->getPrice());
    }

    /**
     * Test SinglePropDataSource with id parameter for referencing.
     */
    public function testSinglePropDataSourceWithId(): void
    {
        new DataMapper(new ServiceResolver());
        
        $article = new Article(1);
        
        $title = $article->getTitle();
        $content = $article->getContent();
        $author = $article->getAuthor();
        
        $this->assertEquals('Introduction to PHP 8', $title);
        $this->assertEquals('PHP 8 brings many new features including attributes...', $content);
        $this->assertEquals('John Doe', $author);
    }

    /**
     * Test that unknown ID returns null values.
     */
    public function testUnknownIdReturnsNull(): void
    {
        new DataMapper(new ServiceResolver());
        
        $product = new Product(999);
        
        $this->assertNull($product->getName());
        $this->assertNull($product->getPrice());
        $this->assertNull($product->getDescription());
    }

    /**
     * Test that properties can be loaded multiple times (cached).
     */
    public function testPropertyLoadingIsCached(): void
    {
        new DataMapper(new ServiceResolver());
        
        $product = new Product(1);
        
        // First call loads the property
        $name1 = $product->getName();
        
        // Second call should return the same cached value
        $name2 = $product->getName();
        
        $this->assertEquals($name1, $name2);
        $this->assertEquals('Laptop', $name1);
    }

    /**
     * Test multiple articles with different IDs.
     */
    public function testMultipleArticles(): void
    {
        new DataMapper(new ServiceResolver());
        
        $article1 = new Article(1);
        $article2 = new Article(2);
        $article3 = new Article(3);
        
        $this->assertEquals('Introduction to PHP 8', $article1->getTitle());
        $this->assertEquals('Advanced Data Mapping', $article2->getTitle());
        $this->assertEquals('Testing Best Practices', $article3->getTitle());
        
        $this->assertEquals('John Doe', $article1->getAuthor());
        $this->assertEquals('Jane Smith', $article2->getAuthor());
        $this->assertEquals('Bob Wilson', $article3->getAuthor());
    }

    /**
     * Test that ID is accessible before loading properties.
     */
    public function testIdAccessibleBeforeLoading(): void
    {
        new DataMapper(new ServiceResolver());
        
        $product = new Product(42);
        
        // ID should be accessible immediately
        $this->assertEquals(42, $product->getId());
        
        // Property loading should work (even if null for unknown ID)
        $this->assertNull($product->getName());
    }
}
