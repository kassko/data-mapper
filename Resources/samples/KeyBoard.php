<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\Hydrator\HydrationContextInterface;
use Kassko\DataMapper\Hydrator\Value;
use Kassko\DataMapper\ObjectExtension\LazyLoadableTrait;
use Kassko\DataMapper\ObjectExtension\LoggableTrait;

class Keyboard
{
    use LazyLoadableTrait;
    //use LoggableTrait;

    /**
     * @DM\Id
     * @DM\Field(readConverter="readBrand")
     */
    private $brand;

    /**
     * @DM\Field
     */
    private $color;

    /**
     * @DM\Provider(lazyLoading=true, class="Solfa\Bundle\ScalesBundle\Scales\ShopManager", method="loadShops")
     * @DM\Field
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
        /*
        if ($logger = $this->getLogger()) {
            var_dump($logger);
        }
        */
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

    public static function readBrand(Value $value, HydrationContextInterface $context)
    {
        $value->value = '===>'.$value->value;
    }
}