<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="sourceA",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\DependencyDataSource",
 *          method="getData"
 *      ),
 *      @DM\DataSource(
 *          id="sourceB",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\DependantDataSource",
 *          method="getData",
 *          depends="propertyA"
 *      )
 * })
 */
class DataSourceDepends
{
    use LoadableTrait;

     /**
     * @DM\RefSource(id="sourceA")
     */
    public $propertyA;
     /**
     * @DM\RefSource(id="sourceB")
     */
    public $propertyB;

    /**
     * Gets the value of propertyA.
     *
     * @return mixed
     */
    public function getPropertyA()
    {
        $this->loadProperty('propertyA');

        return $this->propertyA;
    }

    /**
     * Sets the value of propertyA.
     *
     * @param mixed $propertyA the property
     *
     * @return self
     */
    public function setPropertyA($propertyA)
    {
        $this->propertyA = $propertyA;

        return $this;
    }

    /**
     * Gets the value of propertyB.
     *
     * @return mixed
     */
    public function getPropertyB()
    {
        $this->loadProperty('propertyB');

        return $this->propertyB;
    }

    /**
     * Sets the value of propertyB.
     *
     * @param mixed $propertyB the property
     *
     * @return self
     */
    public function setPropertyB($propertyB)
    {
        $this->propertyB = $propertyB;

        return $this;
    }
}
