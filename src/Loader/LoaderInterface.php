<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Loader;

interface LoaderInterface
{
    /**
     * Load a property on the given object
     *
     * @param object $object The object containing the property
     * @param string $propertyName The name of the property to load
     */
    public function loadProperty(object $object, string $propertyName): void;
}
