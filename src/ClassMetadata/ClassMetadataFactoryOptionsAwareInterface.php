<?php

namespace Kassko\DataAccess\ClassMetadata;

use Doctrine\Common\Cache\Cache as CacheInterface;

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