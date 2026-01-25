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

namespace Kassko\DataMapper\Enum;

/**
 * Enum representing hydration result status.
 * 
 * Used to signal special hydration outcomes that require specific handling,
 * such as skipping property assignment when depth limits are reached.
 */
enum HydrationResult
{
    /**
     * Indicates that the property should be skipped (not set).
     * Used when Loading::depth limit is reached.
     */
    case Skip;
}
