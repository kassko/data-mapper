<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper;

use Psr\Container\ContainerInterface;

interface ServiceLocatorInterface extends ContainerInterface
{
    /**
     * Check if this locator has a mapping for the given key.
     * 
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Get the mapped value for the given key.
     * Returns the service ID (with @) or class name.
     * 
     * @param string $id
     * @return mixed
     */
    public function get(string $id): mixed;
}
