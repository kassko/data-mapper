<?php

namespace Kassko\DataAccess\Listener;

use Kassko\ClassResolver\ContainerAwareClassResolver;
use Kassko\DataAccess\Listener\EventManagerAwareTrait;
use Kassko\DataAccess\Listener\QueryEvent;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Object listener resolver to work with a dependency container.
 *
 * @author kko
 */
class ContainerAwareObjectListenerResolver extends ContainerAwareClassResolver implements ObjectListenerResolverInterface
{
    use EventManagerAwareTrait;

    public function setContainerAwareEventManager(ContainerAwareEventDispatcher $eventManager)
    {
        return $this->setEventManager($eventManager);
    }

    public function registerEvents($className, $eventToRegisterData)
    {
        $classMethods = get_class_methods($className);

        $listenerId = $this->getServiceId($className);
        foreach ($eventToRegisterData as $eventName => $callbackName) {
            if (in_array($callbackName, $classMethods)) {
                $this->registerEvent($listenerId, $eventName, $callbackName);
            }
        }
    }

    public function dispatchEvent($className, $eventName, QueryEvent $event)
    {
        $this->eventManager->dispatch($eventName, $event);
    }

    private function registerEvent($listenerId, $eventName, $callbackName)
    {
        $this->eventManager->addListenerService(
            $eventName,
            [$listenerId, $callbackName]
        );
    }
}
