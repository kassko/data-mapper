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

namespace Kassko\Sample;

class Company
{
    private ?string $name = null;
    private ?Shop $mainShop = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getMainShop(): ?Shop
    {
        return $this->mainShop;
    }

    public function setMainShop(?Shop $mainShop): void
    {
        $this->mainShop = $mainShop;
    }
}
