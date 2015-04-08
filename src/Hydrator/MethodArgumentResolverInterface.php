<?php
namespace Kassko\DataMapper\Hydrator;

/**
* MethodArgumentResolverInterface
*
* @author kko
*/
interface MethodArgumentResolverInterface
{
    public function handle($arg, $object);
}