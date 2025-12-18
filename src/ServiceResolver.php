<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Psr\Container\ContainerInterface;

final class ServiceResolver
{
    /**
     * @param ServiceLocatorInterface[] $locators
     */
    public function __construct(
        private ?ContainerInterface $container,
        private array $locators
    ) {}

    /**
     * Resolve a class/service identifier to an actual instance.
     */
    public function resolve(string $classOrId): object
    {
        // Case 1: Starts with "@" - direct container lookup
        if (str_starts_with($classOrId, '@')) {
            $serviceId = substr($classOrId, 1);
            return $this->resolveFromContainer($serviceId);
        }

        // Case 2: Check locators
        foreach ($this->locators as $locator) {
            if ($locator->has($classOrId)) {
                $resolved = $locator->get($classOrId);
                
                // Recursively resolve (in case locator returns @serviceId)
                return $this->resolve($resolved);
            }
        }

        // Case 3: Try to instantiate directly
        return $this->instantiate($classOrId);
    }

    private function resolveFromContainer(string $serviceId): object
    {
        if ($this->container === null) {
            throw new \RuntimeException(
                "Cannot resolve service identifier '@{$serviceId}' without a container"
            );
        }

        if (!$this->container->has($serviceId)) {
            throw new \RuntimeException(sprintf(
                'Service "%s" not found in container',
                $serviceId
            ));
        }

        return $this->container->get($serviceId);
    }

    private function instantiate(string $className): object
    {
        if (!class_exists($className)) {
            throw new \RuntimeException(
                "DataSource class '{$className}' does not exist"
            );
        }

        $reflection = new \ReflectionClass($className);
        
        if (!$reflection->isInstantiable()) {
            throw new \RuntimeException(
                "DataSource class '{$className}' is not instantiable"
            );
        }

        return new $className();
    }
}
