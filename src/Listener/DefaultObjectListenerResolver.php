<?php

namespace Kassko\DataAccess\Listener;

use Kassko\ClassResolver\DefaultClassResolver;
use Kassko\DataAccess\Listener\EventManagerAwareTrait;
use Kassko\DataAccess\Listener\QueryEvent;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This object listener resolver is used in a chain as fallback
 * when no resolver can resolve a class.
 *
 * @author kko
 */
class DefaultObjectListenerResolver extends DefaultClassResolver implements ObjectListenerResolverInterface
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