<?php
if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('vendor/autoload.php is not found. Please run "composer install".');
}

$loader = require $autoloadFile;
$loader->addPsr4('Kassko\DataMapperTest\\', __DIR__);