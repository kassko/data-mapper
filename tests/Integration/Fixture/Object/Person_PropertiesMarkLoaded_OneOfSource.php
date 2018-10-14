<?php
namespace Kassko\DataMapperTest\Integration\Fixture\Source;

use Kassko\DataMapper\Annotation as DM;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

/**
 * @DM\DataSourcesStore({
 *     @DM\DataSource(
 *          id="initData", supplySeveralFields=true, lazyLoading=true, class="Kassko\DataMapperTest\Integration\Fixture\Source\PersonSource", method="getData"
 *     )
 * });
 */
class Person_PropertiesMarkLoaded_OneOfSource
{
    use LoadableTrait;

    /**
     * @DM\RefSource(id="initData")
     * @DM\Field
     */
    private $firstName;
    /**
     * @DM\RefSource(id="initData")
     * @DM\Field
     */
    private $lastName;

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        $this->markPropertyLoaded('firstName');
    }

    public function getFirstName()
    {
        $this->loadProperty('firstName');
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        $this->loadProperty('lastName');
        return $this->lastName;
    }
}
