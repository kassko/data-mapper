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

use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Entity with DataSource using id parameter for referencing.
 */
class Post
{
    use LoadableTrait;

    private int $id;

    #[DataSource(id: 'postTitle', class: PostDataSource::class, method: 'getTitle', args: ['#id'])]
    private ?string $title = null;

    #[DataSource(id: 'postBody', class: PostDataSource::class, method: 'getBody', args: ['#id'])]
    private ?string $body = null;

    #[DataSource(id: 'postAuthor', class: PostDataSource::class, method: 'getAuthorName', args: ['#id'])]
    private ?string $authorName = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        $this->loadProperty('title');
        return $this->title;
    }

    public function getBody(): ?string
    {
        $this->loadProperty('body');
        return $this->body;
    }

    public function getAuthorName(): ?string
    {
        $this->loadProperty('authorName');
        return $this->authorName;
    }
}
