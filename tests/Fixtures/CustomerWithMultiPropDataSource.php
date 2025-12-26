<?php

declare(strict_types=1);

namespace Kassko\Sample;

use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\ObjectExtension\LoadableInternalTrait;

class CustomerWithMultiPropDataSource 
{
    use LoadableInternalTrait;

    #[MultiPropDataSource(class: '\Kassko\Sample\CustomerMultiPropDataSource', method: 'fetchMultipleData')]
    private ?string $name = null;

    public function getName(): ?string
    {
        $this->loadProperty('name');
        return $this->name;
    }
}
