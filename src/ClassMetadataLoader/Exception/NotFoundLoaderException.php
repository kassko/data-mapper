<?php

namespace Kassko\DataMapper\ClassMetadataLoader\Exception;

use Kassko\DataMapper\ClassMetadataLoader\LoadingCriteriaInterface;

class NotFoundLoaderException extends \RuntimeException
{
    public function __construct(LoadingCriteriaInterface $loadingCriteria)
    {
        parent::__construct(
            sprintf(
                'No loader found or no loader satisfies the following criteria: '
                .'[resourcePath="%s"] - [resourceType="%s"] - [resourceClass="%s"] - [resourceMethod="%s"]',
                $loadingCriteria->getResourcePath(),
                $loadingCriteria->getResourceType(),
                $loadingCriteria->getResourceClass(),
                $loadingCriteria->getResourceMethod()
            )
        );
    }
}
