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

namespace Kassko\DataMapper\Tests\Integration\Features\DeepPathMapping\Fixtures;

use Kassko\DataMapper\Attribute as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Domain object that uses deep path mapping with MultiPropDataSource.
 * 
 * Tests 2-level deep path: "book.title", "book.author.name"
 * Tests 3-level deep path: "book.publisher.location.city"
 */
#[DM\DataSourcesStore(items: [
    new DM\MultiPropDataSource(
        id: 'book_api',
        class: BookApiDataSource::class,
        method: 'fetchBookDetails',
        args: ['#isbn']
    ),
])]
class BookWithFlattenedDetails
{
    use LoadableTrait;

    private string $isbn;

    /**
     * 2-level deep path: book.title
     */
    #[DM\Property(sourceField: 'book.title')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?string $title = null;

    /**
     * 2-level deep path: book.author.name
     */
    #[DM\Property(sourceField: 'book.author.name')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?string $authorName = null;

    /**
     * 2-level deep path: book.author.country
     */
    #[DM\Property(sourceField: 'book.author.country')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?string $authorCountry = null;

    /**
     * 3-level deep path: book.publisher.location.city
     */
    #[DM\Property(sourceField: 'book.publisher.location.city')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?string $publisherCity = null;

    /**
     * 3-level deep path for array: book.metadata._tags
     */
    #[DM\Property(sourceField: 'book.metadata._tags')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?array $tags = null;

    /**
     * 3-level deep path for scalar: book.metadata._rating
     */
    #[DM\Property(sourceField: 'book.metadata._rating')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?float $rating = null;

    /**
     * 2-level deep path: availability.in_stock
     */
    #[DM\Property(sourceField: 'availability.in_stock')]
    #[DM\DataSourceRef(id: 'book_api')]
    private ?bool $inStock = null;

    public function __construct(string $isbn)
    {
        $this->isbn = $isbn;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function getTitle(): ?string
    {
        $this->loadProperty('title');
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        $this->loadProperty('authorName');
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getAuthorCountry(): ?string
    {
        $this->loadProperty('authorCountry');
        return $this->authorCountry;
    }

    public function setAuthorCountry(?string $authorCountry): self
    {
        $this->authorCountry = $authorCountry;
        return $this;
    }

    public function getPublisherCity(): ?string
    {
        $this->loadProperty('publisherCity');
        return $this->publisherCity;
    }

    public function setPublisherCity(?string $publisherCity): self
    {
        $this->publisherCity = $publisherCity;
        return $this;
    }

    public function getTags(): ?array
    {
        $this->loadProperty('tags');
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function getRating(): ?float
    {
        $this->loadProperty('rating');
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function isInStock(): ?bool
    {
        $this->loadProperty('inStock');
        return $this->inStock;
    }

    public function setInStock(?bool $inStock): self
    {
        $this->inStock = $inStock;
        return $this;
    }
}
