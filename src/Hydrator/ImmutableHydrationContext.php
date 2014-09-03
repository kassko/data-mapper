<?php

namespace Kassko\DataAccess\Hydrator;

use LogicException;

/**
 * Hold the hydration context and lock it.
 * This implementation should be used only internally.
 *
 * @author kko
 *
 * @internal
 */
class ImmutableHydrationContext extends HydrationContext
{
	/**
	 * {@inheritdoc}
	 */
	public function setData($key, $value)
	{
		throw $this->createReadOnlyContextException();
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeData($key)
	{
		throw $this->createReadOnlyContextException();
	}

	private function createReadOnlyContextException()
	{
		return new LogicException("Le contexte a déjà été utilisé pour hydrater l'objet. Toute modification de celui-ci est donc inopérante.");
	}
}
