<?php

namespace Kassko\DataAccess\ClassMetadataLoader\Exception;

use Kassko\DataAccess\ClassMetadataLoader\LoadingCriteriaInterface;

class NotFoundLoaderException extends \RuntimeException
{
    public function __construct(LoadingCriteriaInterface $loadingCriteria)
    {
        parent::__construct(
            sprintf(
                '[resourcePath="%s"] - [resourceType="%s"] - [resourceClass="%s"] - [resourceMethod="%s"]',
                $loadingCriteria->getResourcePath(),
                $loadingCriteria->getResourceType(),
                $loadingCriteria->getResourceClass(),
                $loadingCriteria->getResourceMethod()
            )
        );
    }
}
