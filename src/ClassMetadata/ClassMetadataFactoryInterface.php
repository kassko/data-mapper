<?php

namespace Kassko\DataAccess\ClassMetadata;

use Kassko\DataAccess\ClassMetadataLoader\LoadingCriteriaInterface;
use Kassko\DataAccess\Configuration\Configuration;
use Kassko\DataAccess\Configuration\ObjectKey;

/**
* Contract for class metadata factory.
*
* @author kko
*/
interface ClassMetadataFactoryInterface
{
    function loadMetadata(ObjectKey $objectKey, LoadingCriteriaInterface $loadingCriteria, Configuration $configuration);
}
