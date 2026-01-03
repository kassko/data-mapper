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

namespace Kassko\Sample\DataSource;

/**
 * Data source for Post entity.
 */
class PostDataSource
{
    private static array $posts = [
        1 => [
            'title' => 'Welcome to our blog',
            'body' => 'This is the first post on our new blog...',
            'authorName' => 'Admin',
        ],
        2 => [
            'title' => 'PHP 8 Features',
            'body' => 'PHP 8 introduces many exciting features...',
            'authorName' => 'Developer',
        ],
        3 => [
            'title' => 'Testing Guide',
            'body' => 'Learn how to write effective tests...',
            'authorName' => 'QA Engineer',
        ],
    ];

    public function getTitle(int $id): ?string
    {
        return self::$posts[$id]['title'] ?? null;
    }

    public function getBody(int $id): ?string
    {
        return self::$posts[$id]['body'] ?? null;
    }

    public function getAuthorName(int $id): ?string
    {
        return self::$posts[$id]['authorName'] ?? null;
    }
}
