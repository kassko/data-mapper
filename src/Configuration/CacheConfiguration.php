<?php

namespace Kassko\DataAccess\Configuration;

use Doctrine\Common\Cache\Cache as CacheInterface;

/**
 * Hold cache configuration.
 *
 * @author kko
 */
class CacheConfiguration
{
	private $enabled = false;
	private $cache;
	private $lifeTime = 0;

	public function isEnabled()
	{
		return $this->enabled;
	}

	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
		return $this;
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function setCache(CacheInterface $cache)
	{
		$this->cache = $cache;
		return $this;
	}

	public function getLifeTime()
	{
		return $this->lifeTime;
	}

	public function setLifeTime($lifeTime)
	{
		$this->lifeTime = $lifeTime;
		return $this;
	}
}