<?php

namespace Kassko\DataAccess\ClassMetadata;

use Kassko\DataAccess\Configuration\Configuration;

/**
* Contract for class metadata factory.
*
* @author kko
*/
interface ClassMetadataFactoryInterface
{
	function loadMetadata($className, Configuration $config);
}