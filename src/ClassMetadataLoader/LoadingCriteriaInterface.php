<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Abstraction for metadata provider.
 * This interface was planned in advance to avoid incompatibility of user code witch use LoadingCriteria.
 * Declare methods in this interface only when there will be at least two different implementions of LoadingCriteria.
 *
 * @author kko
 */
interface LoadingCriteriaInterface
{
}
