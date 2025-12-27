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

namespace Kassko\DataMapper\Tests\Fixtures;

use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\SinglePropDataSource;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new SinglePropDataSource(
        id: 'cacheEmail',
        class: CacheService::class,
        method: 'getUserData',
        args: ['#userId'],
        priority: 0
    ),
    new SinglePropDataSource(
        id: 'apiEmail',
        class: ApiService::class,
        method: 'getUserData',
        args: ['#userId'],
        priority: 10
    ),
    new MultiPropDataSource(
        id: 'cacheData',
        class: CacheService::class,
        method: 'getUserData',
        args: ['#userId'],
        priority: 0
    ),
    new MultiPropDataSource(
        id: 'apiData',
        class: ApiService::class,
        method: 'getUserData',
        args: ['#userId'],
        priority: 10
    ),
])]
class UserWithPriorityStore
{
    use LoadableTrait;

    private int $userId = 123;

    // Using SinglePropDataSource with different priorities via providers
    #[DataSourceRef(providers: ['cacheEmail', 'apiEmail'])]
    private ?string $email = null;

    // Using MultiPropDataSource with different priorities via providers
    #[DataSourceRef(providers: ['cacheData', 'apiData'])]
    private ?string $firstName = null;

    #[DataSourceRef(providers: ['cacheData', 'apiData'])]
    private ?string $lastName = null;

    public function getEmail(): ?string
    {
        $this->loadProperty('email');
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        $this->loadProperty('lastName');
        return $this->lastName;
    }
}
