<?php

namespace Kassko\DataAccess\ClassMetadata;

use Kassko\DataAccess\Configuration\ObjectKey;

/**
* Contract for class metadata factory.
*
* @author kko
*/
interface ClassMetadataFactoryInterface
{
    function loadMetadata(ObjectKey $objectKey, $resourceType, $resourceDir);
}
