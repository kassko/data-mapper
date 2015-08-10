<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

trait ProcessorTrait
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="dataSource")
     */
    private $property;

    /**
     * @DM\RefSource(id="dataSourceLazyLoaded")
     */
    private $propertyLazyLoaded;

    /**
     * Gets the value of property.
     *
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Sets the value of property.
     *
     * @param mixed $property the property
     *
     * @return self
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Gets the value of propertyLazyLoaded.
     *
     * @return mixed
     */
    public function getPropertyLazyLoaded()
    {
        return $this->propertyLazyLoaded;
    }

    /**
     * Sets the value of propertyLazyLoaded.
     *
     * @param mixed $propertyLazyLoaded the property lazy loaded
     *
     * @return self
     */
    public function setPropertyLazyLoaded($propertyLazyLoaded)
    {
        $this->propertyLazyLoaded = $propertyLazyLoaded;

        return $this;
    }

    public function somePreprocessor()
    {
    }

    public function somePreprocessorA()
    {
    }

    public function somePreprocessorB()
    {
    }

    public function someProcessor()
    {
    }

    public function someProcessorA()
    {
    }

    public function someProcessorB()
    {
    }
}
