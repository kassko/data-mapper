<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="dataSource",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource",
 *          method="getData"
 *      ),
 *      @DM\DataSource(
 *          id="lazyLoadedDataSource",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\PersonDataSource",
 *          method="getLazyLoadedData",
 *          lazyLoading=true
 *      )
 * })
 */
class DataSourcesLoading
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="dataSource")
     */
    public $name;

    /**
     * @DM\RefSource(id="lazyLoadedDataSource")
     */
    public $address;

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of address.
     *
     * @return mixed
     */
    public function getAddress()
    {
        $this->loadProperty('address');
        
        return $this->address;
    }

    /**
     * Sets the value of address.
     *
     * @param mixed $address the address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }
}
