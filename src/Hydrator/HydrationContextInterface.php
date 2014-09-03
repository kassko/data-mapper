<?php

namespace Kassko\DataAccess\Hydrator;

/**
 * Contrat for hydratation context.
 *
 * @author kko
 */
interface HydrationContextInterface
{
	/**
	 * Vérifie qu'une entrée est présente dans le jeu de données.
	 */
	function hasItem($key);

	/**
	 * Récupère une entrée du jeu de donnée.
	 */
	function getItem($key);

	/**
	 * Modifie une entrée du jeu de donnée ou ajoute une nouvelle entrée.
	 */
	function setItem($key, $value);

	/**
	 * Supprime une entrée du jeu de donnée
	 */
	function removeItem($key);
}