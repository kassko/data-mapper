<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\Sample\CustomerWithMultiPropDataSource;
use PHPUnit\Framework\TestCase;

class MultiPropDataSourceTest extends TestCase
{
    protected function tearDown(): void
    {
        LoaderRegistry::clear();
    }

    public function testMultiPropDataSourceIsWellDetected(): void
    {
        new DataMapper(new \Kassko\DataMapper\ServiceResolver());

        $customer = new CustomerWithMultiPropDataSource();

        $this->assertEquals($customer->getName(), 'Nobody' );
    }
}
