<?php

namespace Kassko\DataMapper\Listener;

use Kassko\ClassResolver\ClassResolverChain;
use Kassko\DataMapper\Listener\QueryEvent;

/**
 * Find the good object listener resolver and delegate to it the work.
 *
 * @author kko
 */
class ObjectListenerResolverChain extends ClassResolverChain implements ObjectListenerResolverInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefault(
            (new DefaultObjectListenerResolver)
            ->setEventManager(new \Symfony\Component\EventDispatcher\EventDispatcher)
        );
    }

    public function registerEvents($className, $eventToRegisterData)
    {
        $resolver = $this->findResolver($className);
        $resolver->registerEvents($className, $eventToRegisterData);
    }

    public function dispatchEvent($className, $eventName, QueryEvent $event)
    {
        $resolver = $this->findResolver($className);
        $resolver->dispatchEvent($className, $eventName, $event);
    }
}
