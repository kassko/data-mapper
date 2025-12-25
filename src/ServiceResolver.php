<?php

declare(strict_types=1);

namespace Kassko\DataMapper;

use Psr\Container\ContainerInterface;

final class ServiceResolver
{
    private ?ContainerInterface $container;
    private array $locators;

    /**
     * @param ServiceLocatorInterface|ContainerInterface|null $containerOrLocator PSR-11 container or service locator
     * @param ServiceLocatorInterface[] $additionalLocators Additional service locators (checked in order)
     * @param array<array{object, string}> $factoryServices Factory services: [[$service, 'methodName'], ...]
     * @param array<array{string|object, string}> $staticFactories Static factories: [['ClassName', 'methodName'], ...]
     * @param callable[] $callables Callable factories
     */
    public function __construct(
        ServiceLocatorInterface|ContainerInterface|null $containerOrLocator = null,
        array $additionalLocators = [],
        private array $factoryServices = [],
        private array $staticFactories = [],
        private array $callables = []
    ) {
        if ($containerOrLocator instanceof ServiceLocatorInterface) {
            $this->container = null;
            $this->locators = array_merge([$containerOrLocator], $additionalLocators);
        } elseif ($containerOrLocator instanceof ContainerInterface) {
            $this->container = $containerOrLocator;
            $this->locators = $additionalLocators;
        } else {
            $this->container = null;
            $this->locators = $additionalLocators;
        }
    }

    /**
     * Resolve a class/service identifier to an actual instance.
     * 
     * Search order (from most likely to least likely to have the service):
     * 1. Container (PSR-11) - broadest scope
     * 2. Service locators - domain-specific locators
     * 3. Factory services - instance-based factories
     * 4. Static factories - class-based factories
     * 5. Callables - custom factory functions
     * 6. Direct instantiation - fallback
     */
    public function resolve(string $classOrId): object
    {
        // Case 1: Starts with "@" - service lookup (container or locators)
        if (str_starts_with($classOrId, '@')) {
            $serviceId = substr($classOrId, 1);
            return $this->resolveServiceIdentifier($serviceId);
        }

        // Case 2: Try container first (broadest scope)
        if ($this->container !== null && $this->container->has($classOrId)) {
            return $this->container->get($classOrId);
        }

        // Case 3: Check locators
        foreach ($this->locators as $locator) {
            if ($locator->has($classOrId)) {
                $resolved = $locator->get($classOrId);
                
                // If it's already an object, return it directly
                if (is_object($resolved)) {
                    return $resolved;
                }
                
                // Recursively resolve (in case locator returns @serviceId or class name)
                return $this->resolve($resolved);
            }
        }

        // Case 4: Try factory services
        foreach ($this->factoryServices as [$service, $method]) {
            try {
                $result = $service->$method($classOrId);
                if ($result !== null && is_object($result)) {
                    return $result;
                }
            } catch (\Throwable) {
                // Factory didn't produce the service, continue
            }
        }

        // Case 5: Try static factories
        foreach ($this->staticFactories as [$classOrInstance, $method]) {
            try {
                if (is_object($classOrInstance)) {
                    $result = $classOrInstance->$method($classOrId);
                } else {
                    $result = $classOrInstance::$method($classOrId);
                }
                if ($result !== null && is_object($result)) {
                    return $result;
                }
            } catch (\Throwable) {
                // Factory didn't produce the service, continue
            }
        }

        // Case 6: Try callables
        foreach ($this->callables as $callable) {
            try {
                $result = $callable($classOrId);
                if ($result !== null && is_object($result)) {
                    return $result;
                }
            } catch (\Throwable) {
                // Callable didn't produce the service, continue
            }
        }

        // Case 7: Try to instantiate directly
        return $this->instantiate($classOrId);
    }

    private function resolveServiceIdentifier(string $serviceId): object
    {
        // Try container first
        if ($this->container !== null && $this->container->has($serviceId)) {
            return $this->container->get($serviceId);
        }

        // Try locators
        foreach ($this->locators as $locator) {
            if ($locator->has($serviceId)) {
                $resolved = $locator->get($serviceId);
                
                if (is_object($resolved)) {
                    return $resolved;
                }
                
                if (is_string($resolved) && class_exists($resolved)) {
                    return $this->instantiate($resolved);
                }
            }
        }

        // No container or locator has this service
        throw new \RuntimeException(
            "Cannot resolve service identifier '{$serviceId}' - not found in container or locators"
        );
    }

    private function resolveFromContainer(string $serviceId): object
    {
        if ($this->container === null) {
            throw new \RuntimeException(
                "Cannot resolve service identifier '{$serviceId}' without a container"
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
