<?php

namespace Kassko\DataAccess\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Make class aware of event manager.
 *
 * @author kko
 */
trait EventManagerAwareTrait
{
    protected $eventManager;

    public function setEventManager(EventDispatcherInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }
}
