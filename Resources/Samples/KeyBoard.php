<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

use Kassko\DataAccess\Annotation as OM;
use Kassko\DataAccess\Hydrator\HydrationContextInterface;
use Kassko\DataAccess\Hydrator\Value;
use Kassko\DataAccess\ObjectExtension\LazyLoadableTrait;

class Keyboard
{
    use LazyLoadableTrait;

    /**
     * @OM\Column(readStrategy="readBrand")
     */
    private $brand;

    /**
     * @OM\Column
     */
    private $color;

    /**
     * @OM\Provider(lazyLoading=true, class="Solfa\Bundle\ScalesBundle\Scales\ShopManager", method="loadShops")
     * @OM\Column
     */
    private $shops;

    /**
     * Gets the value of brand.
     *
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Sets the value of brand.
     *
     * @param mixed $brand the brand
     *
     * @return self
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Gets the value of color.
     *
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @param mixed $color the color
     *
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Gets the value of shops.
     *
     * @return mixed
     */
    public function getShops()
    {
        $this->loadProperty('shops');

        return $this->shops;
    }

    /**
     * Sets the value of shops.
     *
     * @param mixed $shops the shops
     *
     * @return self
     */
    public function setShops(array $shops)
    {
        $this->shops = $shops;

        return $this;
    }

    public function readBrand(Value $value, HydrationContextInterface $context)
    {
        $value->value = '===>'.$value->value;
    }
}