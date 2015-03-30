<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loaders = spl_autoload_functions();
foreach ($loaders as $loader) {
    if (is_array($loader) && 2 === count($loader) && $loader[0] instanceof Composer\Autoload\ClassLoader) {
        AnnotationRegistry::registerLoader($loader);
    }
}
