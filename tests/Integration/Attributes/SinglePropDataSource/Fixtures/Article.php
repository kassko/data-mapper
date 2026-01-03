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

use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * Entity with SinglePropDataSource using id for DataSourceRef pattern.
 */
class Article
{
    use LoadableTrait;

    private int $id;

    #[SinglePropDataSource(id: 'articleTitle', class: ArticleDataSource::class, method: 'getTitle', args: ['#id'])]
    private ?string $title = null;

    #[SinglePropDataSource(id: 'articleContent', class: ArticleDataSource::class, method: 'getContent', args: ['#id'])]
    private ?string $content = null;

    #[SinglePropDataSource(id: 'articleAuthor', class: ArticleDataSource::class, method: 'getAuthor', args: ['#id'])]
    private ?string $author = null;

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

    public function getContent(): ?string
    {
        $this->loadProperty('content');
        return $this->content;
    }

    public function getAuthor(): ?string
    {
        $this->loadProperty('author');
        return $this->author;
    }
}
