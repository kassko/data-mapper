<?php

namespace Kassko\DataMapper\ClassMetadata;

use Kassko\DataMapper\Cache\CacheInterface;

/**
* Abstraction for initialize ClassMetadataFactory from configuration
* and to segregate ClassMetadataFactoryInterface
*
* @author kko
*/
interface ClassMetadataFactoryOptionsAwareInterface
{
    function setCache(CacheInterface $cache);
}
