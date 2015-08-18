<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="dataSourceScalarData",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getScalarData",
 *          lazyLoading=true,
 *          supplySeveralFields=false
 *      ),
 * @DM\DataSource(
 *          id="dataSourceArrayData",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getArrayData",
 *          lazyLoading=true,
 *          supplySeveralFields=false
 *      ),
 * @DM\DataSource(
 *          id="dataSourceScalarDataForSeveralFields",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getScalarDataForSeveralFields",
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      ),
 * @DM\DataSource(
 *          id="dataSourceArrayDataForSeveralFields",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getArrayDataForSeveralFields",
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      ),
 * @DM\DataSource(
 *          id="dataSourceObjectData",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getObjectData",
 *          lazyLoading=true,
 *          supplySeveralFields=false
 *      ),
 * @DM\DataSource(
 *          id="dataSourceObjectDataForSeveralFields",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getObjectDataForSeveralFields",
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      ),
 * @DM\DataSource(
 *          id="dataSourceArrayOfObjectData",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getArrayOfObjectData",
 *          lazyLoading=true,
 *          supplySeveralFields=false
 *      ),
 * @DM\DataSource(
 *          id="dataSourceArrayOfObjectDataForSeveralFields",
 *          class="Kassko\DataMapperTest\Hydrator\Fixture\DataSource\SupplySeveralFieldsDataSource",
 *          method="getArrayOfObjectDataForSeveralFields",
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      )
 * })
 */
class DataSourceSupplySeveralFields
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="dataSourceScalarData")
     */
    public $propertyA;
    /**
     * @DM\RefSource(id="dataSourceArrayData")
     */
    public $propertyB;
    /**
     * @DM\RefSource(id="dataSourceScalarDataForSeveralFields")
     */
    public $propertyC;
    /**
     * @DM\RefSource(id="dataSourceScalarDataForSeveralFields")
     */
    public $propertyD;
    /**
     * @DM\RefSource(id="dataSourceArrayDataForSeveralFields")
     */
    public $propertyE;
    /**
     * @DM\RefSource(id="dataSourceArrayDataForSeveralFields")
     */
    public $propertyF;
    /**
     * @DM\RefSource(id="dataSourceObjectData")
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $propertyG;
    /**
     * @DM\RefSource(id="dataSourceObjectDataForSeveralFields")
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $propertyH;
    /**
     * @DM\RefSource(id="dataSourceObjectDataForSeveralFields")
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $propertyI;
    /**
     * @DM\RefSource(id="dataSourceArrayOfObjectData")
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $propertyJ;
    /**
     * @DM\RefSource(id="dataSourceArrayOfObjectDataForSeveralFields")
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $propertyK;
    /**
     * @DM\RefSource(id="dataSourceArrayOfObjectDataForSeveralFields")
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\NestedClass")
     */
    public $propertyL;

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

    /**
     * Gets the value of propertyC.
     *
     * @return mixed
     */
    public function getPropertyC()
    {
        $this->loadProperty('propertyC');
        return $this->propertyC;
    }

    /**
     * Sets the value of propertyC.
     *
     * @param mixed $propertyC the property
     *
     * @return self
     */
    public function setPropertyC($propertyC)
    {
        $this->propertyC = $propertyC;

        return $this;
    }

    /**
     * Gets the value of propertyD.
     *
     * @return mixed
     */
    public function getPropertyD()
    {
        $this->loadProperty('propertyD');
        return $this->propertyD;
    }

    /**
     * Sets the value of propertyD.
     *
     * @param mixed $propertyD the property
     *
     * @return self
     */
    public function setPropertyD($propertyD)
    {
        $this->propertyD = $propertyD;

        return $this;
    }

    /**
     * Gets the value of propertyE.
     *
     * @return mixed
     */
    public function getPropertyE()
    {
        $this->loadProperty('propertyE');
        return $this->propertyE;
    }

    /**
     * Sets the value of propertyE.
     *
     * @param mixed $propertyE the property
     *
     * @return self
     */
    public function setPropertyE($propertyE)
    {
        $this->propertyE = $propertyE;

        return $this;
    }

    /**
     * Gets the value of propertyF.
     *
     * @return mixed
     */
    public function getPropertyF()
    {
        $this->loadProperty('propertyF');
        return $this->propertyF;
    }

    /**
     * Sets the value of propertyF.
     *
     * @param mixed $propertyF the property
     *
     * @return self
     */
    public function setPropertyF($propertyF)
    {
        $this->propertyF = $propertyF;

        return $this;
    }

    /**
     * Gets the value of propertyG.
     *
     * @return mixed
     */
    public function getPropertyG()
    {
        $this->loadProperty('propertyG');
        return $this->propertyG;
    }

    /**
     * Sets the value of propertyG.
     *
     * @param mixed $propertyG the property
     *
     * @return self
     */
    public function setPropertyG($propertyG)
    {
        $this->propertyG = $propertyG;

        return $this;
    }

    /**
     * Gets the value of propertyH.
     *
     * @return mixed
     */
    public function getPropertyH()
    {
        $this->loadProperty('propertyH');
        return $this->propertyH;
    }

    /**
     * Sets the value of propertyH.
     *
     * @param mixed $propertyH the property
     *
     * @return self
     */
    public function setPropertyH($propertyH)
    {
        $this->propertyH = $propertyH;

        return $this;
    }

    /**
     * Gets the value of propertyI.
     *
     * @return mixed
     */
    public function getPropertyI()
    {
        $this->loadProperty('propertyI');
        return $this->propertyI;
    }

    /**
     * Sets the value of propertyI.
     *
     * @param mixed $propertyI the property
     *
     * @return self
     */
    public function setPropertyI($propertyI)
    {
        $this->propertyI = $propertyI;

        return $this;
    }

    /**
     * Gets the value of propertyJ.
     *
     * @return mixed
     */
    public function getPropertyJ()
    {
        $this->loadProperty('propertyJ');
        return $this->propertyJ;
    }

    /**
     * Sets the value of propertyJ.
     *
     * @param mixed $propertyJ the property
     *
     * @return self
     */
    public function setPropertyJ($propertyJ)
    {
        $this->propertyJ = $propertyJ;

        return $this;
    }

    /**
     * Gets the value of propertyK.
     *
     * @return mixed
     */
    public function getPropertyK()
    {
        $this->loadProperty('propertyK');
        return $this->propertyK;
    }

    /**
     * Sets the value of propertyK.
     *
     * @param mixed $propertyK the property
     *
     * @return self
     */
    public function setPropertyK($propertyK)
    {
        $this->propertyK = $propertyK;

        return $this;
    }

    /**
     * Gets the value of propertyL.
     *
     * @return mixed
     */
    public function getPropertyL()
    {
        $this->loadProperty('propertyL');
        return $this->propertyL;
    }

    /**
     * Sets the value of propertyL.
     *
     * @param mixed $propertyL the property
     *
     * @return self
     */
    public function setPropertyL($propertyL)
    {
        $this->propertyL = $propertyL;

        return $this;
    }
}
