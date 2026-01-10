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

namespace Kassko\Sample\MultiPropConflict;

/**
 * Service that provides avatar data for a person.
 * This is an independent source that only returns avatar.
 */
class AvatarService
{
    public function getAvatar(int $id): string
    {
        return 'avatar-from-avatar-service.png';
    }
}
