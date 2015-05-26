<?php

namespace Kassko\DataMapper\MethodInvoker;

/**
 * An invoker which allows to invoke the magic method __call.
 *
 * @author kko
 */
class MagicMethodInvoker extends MethodInvoker
{
    protected function isInvocable($object, $method, $args)
    {
        return method_exists($object, '__call') || (method_exists($object, $method) && is_callable([$object, $method]));
    }
}
