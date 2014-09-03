<?php

namespace Kassko\DataAccess\ClassMetadataLoader;

use Kassko\DataAccess\Exception\ObjectMappingException;
use Kassko\DataAccess\ClassMetadata\ClassMetadata;

/**
 * Find the good class metadata loader and delegate to it class metadata loading.
 *
 * @author kko
 */
class DelegatingLoader implements LoaderInterface
{
	private $resolver;

	public function __construct(LoaderResolverInterface $resolver)
	{
		$this->resolver = $resolver;
	}

	public function loadObjectMetadata(ClassMetadata $objectMetadata, $ressource, $type = null)
    {
    	if (false === $loader = $this->resolver->resolveLoader($ressource, $type)) {
        	throw ObjectMappingException::notFoundDriverException($ressource);
        }

        return $loader->loadObjectMetadata($objectMetadata, $ressource, $type);
    }

    public function supports($ressource, $type = null)
    {
    	return false !== $this->resolver->resolveLoader($ressource, $type);
    }
}