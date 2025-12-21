<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Kassko\DataMapper\LazyLoader\LazyLoader;
use Kassko\DataMapper\Registry\LazyLoaderRegistry;
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
        $lazyLoader = new LazyLoader($serviceResolver);
        
        // Register the LazyLoader globally
        LazyLoaderRegistry::set($lazyLoader);
        
        return new DataMapper($serviceResolver);
    }
}
