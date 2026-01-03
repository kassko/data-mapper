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
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Abstract base class for portable integration tests.
 * 
 * This class provides a standardized way to write integration tests that can be
 * executed both in native PHP context and within a Symfony Bundle context.
 * 
 * Tests extending this class can be reused by the data-mapper-bundle to verify
 * that the bundle correctly integrates with the core library.
 * 
 * Usage:
 *   1. Extend this class for your integration test
 *   2. Use getDataMapper() to obtain a DataMapper instance
 *   3. The provider will be automatically set based on the test environment
 * 
 * Environment:
 *   - By default, uses NativeDataMapperProvider
 *   - The data-mapper-bundle overrides with SymfonyDataMapperProvider
 */
abstract class PortableIntegrationTestCase extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private static ?DataMapperProviderInterface $provider = null;

    /**
     * Set the DataMapper provider for tests.
     * 
     * This method is called by the test runner to inject the appropriate provider.
     * By default, it uses NativeDataMapperProvider.
     */
    public static function setDataMapperProvider(DataMapperProviderInterface $provider): void
    {
        self::$provider = $provider;
    }

    /**
     * Get the current DataMapper provider.
     */
    protected static function getProvider(): DataMapperProviderInterface
    {
        if (self::$provider === null) {
            self::$provider = new NativeDataMapperProvider();
        }

        return self::$provider;
    }

    /**
     * Get a DataMapper instance configured for the current test environment.
     * 
     * @param array<string, object> $services Services to register
     * @param array<string, mixed> $config Additional configuration
     */
    protected function getDataMapper(array $services = [], array $config = []): DataMapper
    {
        return self::getProvider()->getDataMapper($services, $config);
    }

    /**
     * Get the underlying container if available.
     */
    protected function getContainer(): ?ContainerInterface
    {
        return self::getProvider()->getContainer();
    }

    /**
     * Check if running in Symfony context.
     */
    protected function isSymfonyContext(): bool
    {
        return self::getProvider()->getName() === 'symfony';
    }

    /**
     * Check if running in native PHP context.
     */
    protected function isNativeContext(): bool
    {
        return self::getProvider()->getName() === 'native';
    }

    /**
     * Skip test if not in Symfony context.
     */
    protected function requiresSymfonyContext(string $reason = ''): void
    {
        if (!$this->isSymfonyContext()) {
            $message = 'This test requires Symfony context';
            if ($reason) {
                $message .= ': ' . $reason;
            }
            $this->markTestSkipped($message);
        }
    }

    /**
     * Skip test if not in native context.
     */
    protected function requiresNativeContext(string $reason = ''): void
    {
        if (!$this->isNativeContext()) {
            $message = 'This test requires native PHP context';
            if ($reason) {
                $message .= ': ' . $reason;
            }
            $this->markTestSkipped($message);
        }
    }

    protected function tearDown(): void
    {
        self::getProvider()->tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Reset provider after all tests in the class
        self::$provider = null;
        parent::tearDownAfterClass();
    }
}
