<?php

namespace Kassko\DataAccess\Hydrator;

use InvalidArgumentException;

/**
 * Hold hydration context.
 *
 * @author kko
 */
class HydrationContext implements HydrationContextInterface
{
	protected $data;

	public function __construct(array &$data)
	{
		$this->data = &$data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasItem($key)
	{
		return array_key_exists($key, $this->data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItem($key)
	{
		if (! array_key_exists($key, $this->data)) {
			throw new InvalidArgumentException(sprintif('Clé incorrecte. Valeur [%s] fournie.', $key));
		}

		return $this->data[$key];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setItem($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeItem($key)
	{
		unset($this->data[$key]);
	}
}
