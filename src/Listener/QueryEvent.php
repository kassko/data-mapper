<?php

namespace Kassko\DataMapper\Listener;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event used by object listener resolver.
 *
 * @author kko
 */
class QueryEvent extends GenericEvent
{
    /**
     * @var object Object or collection
     */
    protected $result;

    public function __construct($result, array $arguments = [])
    {
        parent::__construct($result, $arguments);

        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }
}
