<?php

namespace Kassko\DataMapperTest\Hydrator\Fixture\Model;

use Kassko\DataMapper\Annotation as DM;

class PersonB
{
    /**
     * @DM\Field(class="Kassko\DataMapperTest\Hydrator\Fixture\Model\Address")
     */
    public $address;
}
