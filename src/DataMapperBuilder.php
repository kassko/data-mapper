<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Psr\Container\ContainerInterface;

final class DataMapperBuilder
{
    private ?ContainerInterface $container = null;
    
    /** @var ServiceLocatorInterface[] */
    private array $locators = [];

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    public function addLocator(ServiceLocatorInterface $locator): self
    {
        $this->locators[] = $locator;
        return $this;
    }

    public function build(): DataMapper
    {
        $serviceResolver = new ServiceResolver($this->container, $this->locators);
        return new DataMapper($serviceResolver);
    }
}
