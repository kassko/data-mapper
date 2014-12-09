<?php

namespace Kassko\DataMapper\Listener;

use Kassko\DataMapper\Listener\QueryEvent;
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
