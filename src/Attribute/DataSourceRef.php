<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class DataSourceRef
{
    public readonly ?string $id;
    public readonly ?array $chain;
    public readonly ?array $providers;
    public readonly ?string $exception;

    public function __construct(
        ?string $id = null,
        ?array $chain = null,
        ?array $providers = null,
        ?string $exception = null
    ) {
        // Validation: chain and exception must both be present or both absent (check first)
        if (($chain !== null) !== ($exception !== null)) {
            throw new \InvalidArgumentException('DataSourceRef: chain and exception must both be present or both absent');
        }
        
        // Count how many of the mutually exclusive options are set
        $count = ($id !== null ? 1 : 0) + ($chain !== null ? 1 : 0) + ($providers !== null ? 1 : 0);
        
        // Validation: at least one must be set
        if ($count === 0) {
            throw new \InvalidArgumentException('DataSourceRef requires one of: id, chain, or providers');
        }
        
        // Validation: only one can be set
        if ($count > 1) {
            throw new \InvalidArgumentException('DataSourceRef: id, chain, and providers are mutually exclusive');
        }
        
        $this->id = $id;
        $this->chain = $chain;
        $this->providers = $providers;
        $this->exception = $exception;
    }
}
