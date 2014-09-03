<?php

namespace Kassko\DataAccess\Listener;

use Kassko\ClassResolver\FactoryClassResolver;
use Kassko\DataAccess\Listener\EventManagerAwareTrait;
use Kassko\DataAccess\Listener\QueryEvent;

/**
 * Object listener resolver to work with a factory.
 *
 * @author kko
 */
class FactoryObjectListenerResolver extends FactoryClassResolver implements ObjectListenerResolverInterface
{
	use EventManagerAwareTrait;

	public function registerEvents($className, $eventToRegisterData)
	{
		$factory = $this->findFactory($className);

		$classMethods = get_class_methods($className);
        $listenerInstance = $factory->getInstance($className);
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