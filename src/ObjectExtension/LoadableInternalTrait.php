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

namespace Kassko\DataMapper\ObjectExtension;

use Kassko\DataMapper\Registry\LoaderRegistry;

/**
 * Internal trait for test fixtures and internal examples.
 * 
 * This trait extends LoadableTrait with internal methods needed for eager loading.
 * DO NOT use this trait in production code - use LoadableTrait instead.
 * 
 * @internal This trait is for internal library use only
 */
trait LoadableInternalTrait
{
    use LoadableTrait;
    
    /**
     * Load all eager properties. 
     * @internal This method is for internal use by the data mapper
     */
    public function loadEagerProperties(): void
    {
        $loader = LoaderRegistry::get();
        
        if ($loader === null) {
            return;
        }
        
        $loader->loadEagerProperties($this);
    }
}
