<?php

namespace Kassko\DataAccess\Listener;

use Kassko\DataAccess\Listener\QueryEvent;
use Kassko\ClassResolver\ClassResolverInterface;

/**
 * Contract for object listener resolvers.
 *
 * @author kko
 */
interface ObjectListenerResolverInterface
{
    function registerEvents($className, $eventToRegisterData);
    function dispatchEvent($className, $eventName, QueryEvent $event);
}
