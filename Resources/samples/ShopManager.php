<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

use Solfa\Bundle\ScalesBundle\Scales\Keyboard;

class ShopManager
{
    public function loadShops(Keyboard $keyboard)
    {var_dump('ici loadshops');
        $keyboard->setShops(['shop 1', 'shop 2']);
    }
}