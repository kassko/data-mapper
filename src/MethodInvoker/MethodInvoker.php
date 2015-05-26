<?php

namespace Kassko\DataMapper\MethodInvoker;

use Kassko\DataMapper\Cache\CacheProfile;

/**
 * An invoker to optimize callable invocation.
 *
 * @author kko
 */
class MethodInvoker
{
    public function invoke($object, $method = '__invoke', $args = [], CacheProfile $cacheProfile = null)
    {
        if (null === $cacheProfile) {
            return $this->doInvoke($object, $method, $args);  
        }   

        return $cacheProfile->execute(function () use ($object, $method, $args) {
            return $this->doInvoke($object, $method, $args);
        }); 
    }

    private function doInvoke($object, $method = '__invoke', $args = [])
    {
        if (! $this->isInvocable($object, $method, $args)) {
            throw new \BadMethodCallException(sprintf('Failure on call method "%s::%s".', get_class($object), $method));
        }

        switch (count($args))
        {
            case 0:
                $result = $object->$method();
                break;
            case 1:
                $result = $object->$method($args[0]);
                break;
            case 2:
                $result = $object->$method($args[1]);
                break;
            case 3:
                $result = $object->$method($args[2]);
                break;
            case 4:
                $result = $object->$method($args[3]);
                break;
            default:
                $result = call_user_func_array([$object, $method], $args);
        }

        return $result;
    }

    protected function isInvocable($object, $method, $args)
    {
        return method_exists($object, $method) && is_callable([$object, $method]);
    }
}
