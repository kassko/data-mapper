<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

use Kassko\DataMapper\Annotation as DM;

class RgbKeyboard
{
    /**
     * @DM\Field
     * @DM\Id
     */
    private $brand;

    /**
     * @DM\Field
     * @DM\ValueObject(class="Solfa\Bundle\ScalesBundle\Scales\Color", mappingResourceType="yaml_file", mappingResourceName="colorEn.yml")
     */
    private $colorEn;

    /**
     * @DM\Field
     * @DM\ValueObject(class="Solfa\Bundle\ScalesBundle\Scales\Color", mappingResourceType="yaml_file", mappingResourceName="colorFr.yml")
     */
    private $colorFr;

    /**
     * @DM\Field
     * @DM\ValueObject(class="Solfa\Bundle\ScalesBundle\Scales\Color", mappingResourceType="yaml_file", mappingResourceName="colorEs.yml")
     */
    private $colorEs;


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
    public function getColorEn()
    {
        return $this->colorEn;
    }

    /**
     * Sets the value of color.
     *
     * @param mixed $color the color
     *
     * @return self
     */
    public function setColorEn($color)
    {
        $this->colorEn = $color;

        return $this;
    }

    /**
     * Gets the value of color.
     *
     * @return mixed
     */
    public function getColorFr()
    {
        return $this->colorFr;
    }

    /**
     * Sets the value of color.
     *
     * @param mixed $color the color
     *
     * @return self
     */
    public function setColorFr($color)
    {
        $this->colorFr = $color;

        return $this;
    }

    /**
     * Gets the value of color.
     *
     * @return mixed
     */
    public function getColorEs()
    {
        return $this->colorEs;
    }

    /**
     * Sets the value of color.
     *
     * @param mixed $color the color
     *
     * @return self
     */
    public function setColorEs($color)
    {
        $this->colorEs = $color;

        return $this;
    }
}
