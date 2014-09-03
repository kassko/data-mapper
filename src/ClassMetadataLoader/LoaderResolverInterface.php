<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

/**
 * Contract to find the good clas metadata loader.
 *
 * @author kko
 */
interface LoaderResolverInterface
{
	function resolveLoader($objectClassName, $ressource);
}