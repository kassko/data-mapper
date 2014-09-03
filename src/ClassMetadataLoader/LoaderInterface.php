<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Kassko\DataAccess\ClassMetadata\ClassMetadata;

/**
 * Contract for class metadata loaders.
 *
 * @author kko
 */
interface LoaderInterface
{
	function loadObjectMetadata(ClassMetadata $metadata, $ressource, $type = null);
	function supports($ressource, $type = null);
}