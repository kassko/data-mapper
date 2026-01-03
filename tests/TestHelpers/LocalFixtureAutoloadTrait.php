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

/**
 * Trait to dynamically load fixtures from a local Fixtures/ directory.
 * 
 * This trait registers a custom autoloader that loads classes from the Fixtures/
 * directory relative to the test file location.
 * 
 * Usage:
 *   1. Use this trait in your test class
 *   2. Ensure your test class has a Fixtures/ subdirectory
 *   3. Classes in Fixtures/ should use namespace Kassko\Sample\<anything>\
 *      or Kassko\DataMapper\Tests\Fixtures\
 */
trait LocalFixtureAutoloadTrait
{
    private static ?\Closure $fixtureAutoloader = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        $fixturesDir = dirname((new \ReflectionClass(static::class))->getFileName()) . '/Fixtures';
        
        if (is_dir($fixturesDir)) {
            self::$fixtureAutoloader = function (string $class) use ($fixturesDir): void {
                // Handle Kassko\Sample\* namespaces (with any sub-namespace)
                if (str_starts_with($class, 'Kassko\\Sample\\')) {
                    $className = substr($class, strrpos($class, '\\') + 1);
                    $path = $fixturesDir . '/' . $className . '.php';
                    if (file_exists($path)) {
                        require $path;
                    }
                }
                // Handle Kassko\DataMapper\Tests\Fixtures\* namespace
                elseif (str_starts_with($class, 'Kassko\\DataMapper\\Tests\\Fixtures\\')) {
                    $className = substr($class, strrpos($class, '\\') + 1);
                    $path = $fixturesDir . '/' . $className . '.php';
                    if (file_exists($path)) {
                        require $path;
                    }
                }
            };
            
            spl_autoload_register(self::$fixtureAutoloader, true, true);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$fixtureAutoloader !== null) {
            spl_autoload_unregister(self::$fixtureAutoloader);
            self::$fixtureAutoloader = null;
        }
        
        parent::tearDownAfterClass();
    }
}
