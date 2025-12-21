<?php

declare(strict_types=1);

namespace Kassko\Sample;

class ChainDataSource
{
    public function failingSource(): string
    {
        throw new UnsuitableSourceException('Source A failed');
    }
    
    public function successfulSource(): string
    {
        return 'data-from-B';
    }
    
    public function unexpectedFailure(): string
    {
        throw new \LogicException('Unexpected error');
    }
}
