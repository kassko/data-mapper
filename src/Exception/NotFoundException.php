<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
