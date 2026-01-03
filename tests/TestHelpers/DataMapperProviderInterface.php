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

namespace Kassko\DataMapper\Tests\TestHelpers;

use Kassko\DataMapper\DataMapper;
use Psr\Container\ContainerInterface;

/**
 * Interface for providing DataMapper instances in tests.
 * 
 * This abstraction allows integration tests to be reused across different
 * environments (native PHP, Symfony, etc.) by injecting different providers.
 */
interface DataMapperProviderInterface
{
    /**
     * Get or create a DataMapper instance.
     * 
     * @param array<string, object> $services Services to register (serviceId => instance)
     * @param array<string, mixed> $config Additional configuration options
     */
    public function getDataMapper(array $services = [], array $config = []): DataMapper;

    /**
     * Get the underlying service container (if available).
     * 
     * This is useful for tests that need to verify container integration.
     * Returns null for native PHP tests without a container.
     */
    public function getContainer(): ?ContainerInterface;

    /**
     * Clean up after tests.
     */
    public function tearDown(): void;

    /**
     * Get the provider name for debugging/logging purposes.
     */
    public function getName(): string;
}
