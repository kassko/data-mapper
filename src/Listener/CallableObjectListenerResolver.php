<?php

namespace Kassko\DataMapper\Listener;

use Kassko\ClassResolver\CallableClassResolver;
use Kassko\DataMapper\Listener\EventManagerAwareTrait;
use Kassko\DataMapper\Listener\QueryEvent;

/**
 * Object listener resolver which allows to work with a callable.
 *
 * @author kko
 */
class CallableObjectListenerResolver extends CallableClassResolver implements ObjectListenerResolverInterface
{
    use EventManagerAwareTrait;

    public function registerEvents($className, $eventToRegisterData)
    {
        $classMethods = get_class_methods($className);
        $listenerInstance = $this->resolve($className);
        foreach ($eventToRegisterData as $eventName => $callbackName) {
            if (in_array($callbackName, $classMethods)) {
                $this->registerEvent($listenerInstance, $eventName, $callbackName);
            }
        }
    }

    public function dispatchEvent($className, $eventName, QueryEvent $event)
    {
        $this->eventManager->dispatch($eventName, $event);
    }

    private function registerEvent($listener, $eventName, $callbackName)
    {
        $this->eventManager->addListener(
            $eventName,
            [$listener, $callbackName]
        );
    }
}
