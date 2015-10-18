<?php
namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *      @DM\DataSource(
 *          id="source",
 *          class="DataSourceMock",
 *          method="getData",
 *			args={"#propertyA"}
 *      )
 * })
 */
class CaseDataSourceWithArgsToResolve
{
	use LoadableTrait;

    /**
     * @DM\Field
     */
    public $propertyA;

    /**
	 * @DM\RefSource(id="source")
	 * @DM\Field
	 */
    public $propertyB;


    public function setPropertyA($propertyA)
    {
        $this->propertyA = $propertyA;

        return $this;
    }

    public function getPropertyB()
    {
    	$this->loadProperty('propertyB');

        return $this->propertyB;
    }

    public function setPropertyB($propertyB)
    {
        $this->propertyB = $propertyB;

        return $this;
    }
}
