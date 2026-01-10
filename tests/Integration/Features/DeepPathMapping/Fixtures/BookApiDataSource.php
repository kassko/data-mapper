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

/**
 * Mock data source that returns nested raw data for testing deep path mapping.
 * 
 * Simulates an API response with nested structure.
 */
class BookApiDataSource
{
    /**
     * Fetch book details - returns nested data structure.
     * 
     * @param string $isbn The book ISBN
     * @return array<string, mixed> Nested data with book details
     */
    public function fetchBookDetails(string $isbn): array
    {
        // Simulate API response with nested structure
        return [
            'book' => [
                'title' => 'Clean Code',
                'author' => [
                    'name' => 'Robert C. Martin',
                    'country' => 'USA',
                ],
                'publisher' => [
                    'name' => 'Prentice Hall',
                    'location' => [
                        'city' => 'Boston',
                        'country' => 'USA',
                    ],
                ],
                'metadata' => [
                    '_tags' => ['programming', 'software', 'best-practices'],
                    '_rating' => 4.8,
                    '_reviews_count' => 1250,
                ],
            ],
            'availability' => [
                'in_stock' => true,
                'quantity' => 42,
            ],
        ];
    }

    /**
     * Fetch book reviews - returns a single nested value.
     * 
     * @param string $isbn The book ISBN
     * @return array<string, mixed> Nested data
     */
    public function fetchBookReviews(string $isbn): array
    {
        return [
            'reviews' => [
                'items' => [
                    ['author' => 'John', 'rating' => 5, 'text' => 'Excellent book!'],
                    ['author' => 'Jane', 'rating' => 4, 'text' => 'Very useful.'],
                ],
                'average_rating' => 4.5,
                'total_count' => 2,
            ],
        ];
    }
}
