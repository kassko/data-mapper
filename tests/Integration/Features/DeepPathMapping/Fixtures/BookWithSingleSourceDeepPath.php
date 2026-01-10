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
 * Domain object that uses deep path mapping with SinglePropDataSource.
 * 
 * Each property has its own data source that returns nested data,
 * and the deep path is used to extract the specific value.
 */
class BookWithSingleSourceDeepPath
{
    use LoadableTrait;

    private string $isbn;

    /**
     * SinglePropDataSource returns nested data, deep path extracts specific value.
     */
    #[DM\Property(sourceField: 'reviews.average_rating')]
    #[DM\SinglePropDataSource(
        class: BookApiDataSource::class,
        method: 'fetchBookReviews',
        args: ['#isbn']
    )]
    private ?float $averageRating = null;

    /**
     * SinglePropDataSource returns nested array.
     */
    #[DM\Property(sourceField: 'reviews.items')]
    #[DM\SinglePropDataSource(
        class: BookApiDataSource::class,
        method: 'fetchBookReviews',
        args: ['#isbn']
    )]
    private ?array $reviewItems = null;

    /**
     * SinglePropDataSource with 2-level deep path.
     */
    #[DM\Property(sourceField: 'reviews.total_count')]
    #[DM\SinglePropDataSource(
        class: BookApiDataSource::class,
        method: 'fetchBookReviews',
        args: ['#isbn']
    )]
    private ?int $totalReviewsCount = null;

    public function __construct(string $isbn)
    {
        $this->isbn = $isbn;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function getAverageRating(): ?float
    {
        $this->loadProperty('averageRating');
        return $this->averageRating;
    }

    public function setAverageRating(?float $averageRating): self
    {
        $this->averageRating = $averageRating;
        return $this;
    }

    public function getReviewItems(): ?array
    {
        $this->loadProperty('reviewItems');
        return $this->reviewItems;
    }

    public function setReviewItems(?array $reviewItems): self
    {
        $this->reviewItems = $reviewItems;
        return $this;
    }

    public function getTotalReviewsCount(): ?int
    {
        $this->loadProperty('totalReviewsCount');
        return $this->totalReviewsCount;
    }

    public function setTotalReviewsCount(?int $totalReviewsCount): self
    {
        $this->totalReviewsCount = $totalReviewsCount;
        return $this;
    }
}
