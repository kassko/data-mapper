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

namespace Kassko\Sample\SinglePropDataSource;

/**
 * Data source for Article entity.
 */
class ArticleDataSource
{
    private static array $articles = [
        1 => [
            'title' => 'Introduction to PHP 8',
            'content' => 'PHP 8 brings many new features including attributes...',
            'author' => 'John Doe',
        ],
        2 => [
            'title' => 'Advanced Data Mapping',
            'content' => 'Learn about lazy loading and hydration patterns...',
            'author' => 'Jane Smith',
        ],
        3 => [
            'title' => 'Testing Best Practices',
            'content' => 'Integration tests are essential for verifying behavior...',
            'author' => 'Bob Wilson',
        ],
    ];

    public function getTitle(int $id): ?string
    {
        return self::$articles[$id]['title'] ?? null;
    }

    public function getContent(int $id): ?string
    {
        return self::$articles[$id]['content'] ?? null;
    }

    public function getAuthor(int $id): ?string
    {
        return self::$articles[$id]['author'] ?? null;
    }
}
