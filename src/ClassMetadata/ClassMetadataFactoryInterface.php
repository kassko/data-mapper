<?php

namespace Kassko\DataMapper\ClassMetadata;

use Kassko\DataMapper\ClassMetadataLoader\LoadingCriteriaInterface;
use Kassko\DataMapper\Configuration\Configuration;
use Kassko\DataMapper\Configuration\ObjectKey;

/**
* Contract for class metadata factory.
*
* @author kko
*/
interface ClassMetadataFactoryInterface
{
    function loadMetadata(ObjectKey $objectKey, LoadingCriteriaInterface $loadingCriteria, Configuration $configuration);
}
