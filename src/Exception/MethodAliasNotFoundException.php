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

namespace Kassko\DataMapper\Exception;

/**
 * Exception thrown when a referenced MethodAlias is not found.
 */
class MethodAliasNotFoundException extends \RuntimeException
{
    public function __construct(string $aliasName, string $className)
    {
        parent::__construct(sprintf(
            'MethodAlias "%s" not found in class "%s" or its parent classes. '
            . 'Make sure to define the MethodAlias attribute on the class.',
            $aliasName,
            $className
        ));
    }
}
