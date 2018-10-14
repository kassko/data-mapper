<?php

namespace Kassko\DataMapperTest\Integration;

use Kassko\DataMapperTest\Integration\Fixture;

class SomeObjectLoadingTest extends ObjectTestCase
{
    public function setup()
    {
        parent::init();
    }

	/**
      * @test
      */
    public function explicitPropertyAssignmentsAreIgnored()
    {
        $person = new Fixture\Object\Person_NoPropertyMarkLoaded;
        $person->setFirstName('Brian');
        $person->setLastName('Johnson');

        $this->assertEquals('Daniel', $person->getFirstName());
        $this->assertEquals('Jackson', $person->getLastName());
    }

    /**
     * @return array
     */
    public function personPropertiesMarkLoadedProvider()
    {
        return [
            Fixture\Object\Person_PropertiesMarkLoaded_AllOfSource::class,
            Fixture\Object\Person_PropertiesMarkLoaded_OneOfSource::class,
        ];
    }

    /**
      * @test
      * @dataProvider personPropertiesMarkLoadedProvider
      *
      * @param string A person class name
      */
    public function explicitPropertyAssignmentsAreConsideredAndNoLoadingFromDataSource($fullClass)
    {
        $person = new $fullClass;
        $person->setFirstName('Brian');
        $person->setLastName('Johnson');

        $this->assertEquals('Brian', $person->getFirstName());
        $this->assertEquals('Johnson', $person->getLastName());

        $person = new $fullClass;
        $person->setFirstName('Brian');

        $this->assertEquals('Brian', $person->getFirstName());
        $this->assertNull($person->getLastName());
    }
}
