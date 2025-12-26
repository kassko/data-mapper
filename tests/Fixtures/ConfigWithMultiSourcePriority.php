<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Fixtures;

use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    // Defaults - lowest priority
    new MultiPropDataSource(
        id: 'defaults',
        class: DefaultsService::class,
        method: 'getDefaults',
        priority: 0
    ),
    // Cache - medium priority
    new MultiPropDataSource(
        id: 'cache',
        class: CacheService::class,
        method: 'getConfig',
        priority: 5
    ),
    // API - highest priority
    new MultiPropDataSource(
        id: 'api',
        class: ApiService::class,
        method: 'getConfig',
        priority: 10
    ),
])]
class ConfigWithMultiSourcePriority
{
    use LoadableTrait;

    // All three sources will hydrate, priority determines final values
    #[DataSourceRef(providers: ['defaults', 'cache', 'api'])]
    private ?string $theme = null;

    #[DataSourceRef(providers: ['defaults', 'cache', 'api'])]
    private ?string $language = null;

    #[DataSourceRef(providers: ['defaults', 'cache'])]
    private ?string $timezone = null;
    // timezone not in api, so cache value should win

    #[DataSourceRef(providers: ['defaults', 'api'])]
    private ?bool $notifications = null;
    // notifications not in cache, so api value should win

    public function getTheme(): ?string
    {
        $this->loadProperty('theme');
        return $this->theme;
    }

    public function getLanguage(): ?string
    {
        $this->loadProperty('language');
        return $this->language;
    }

    public function getTimezone(): ?string
    {
        $this->loadProperty('timezone');
        return $this->timezone;
    }

    public function getNotifications(): ?bool
    {
        $this->loadProperty('notifications');
        return $this->notifications;
    }
}
