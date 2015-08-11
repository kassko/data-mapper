<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="dataSource",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\ParametersDataSource",
 *          method="getData",
 *          args={"##this", "##data", "#propertyB", 12, "aaa"}
 *      )
 * })
 */
class DataSourcesParameters
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="dataSource")
     */
    private $propertyA;
    private $propertyB = 'property_b_value';

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
     * @param mixed $property the propertyA
     *
     * @return self
     */
    public function setPropertyA($propertyA)
    {
        $this->propertyA = $propertyA;

        return $this;
    }
}
