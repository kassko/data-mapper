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
class DataSourceParameters
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="dataSource")
     */
    private $propertyA;
    private $propertyB = 'propertyBValue';

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
