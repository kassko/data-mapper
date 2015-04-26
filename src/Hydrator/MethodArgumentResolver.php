<?php
namespace Kassko\DataMapper\Hydrator;

use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Hydrator\AbstractHydrator;
use Kassko\DataMapper\Hydrator\Exception\UnexpectedMethodArgumentException;

/**
* MethodArgumentResolver
*
* @author kko
*/
class MethodArgumentResolver
{
    private static $serviceMarker = '@';
    private static $serviceMarkerSize = 1;
    private static $fieldMarker = '#';
    private static $fieldMarkerSize = 1;
    
    private $object;
    private $hydrator;
    private $metadata;
    private $classResolver;

    public function __construct(AbstractHydrator $hydrator, ClassMetadata $metadata, ClassResolverInterface $classResolver = null)
    {        
        $this->hydrator = $hydrator;
        $this->metadata = $metadata;
        $this->classResolver = $classResolver;
    }

    public function handle($arg, $object)
    {
        $this->object = $object;

        if ('##this' === $arg) {
            return $this->object; 
        } 

        if (self::$fieldMarker === $arg[0]) {
            return $this->resolveFieldValue(substr($arg, self::$fieldMarkerSize));
        } 

        if (self::$serviceMarker === $arg[0]) {
            return $this->resolveService($arg);
        }

        throw new UnexpectedMethodArgumentException($arg);
    }

    public function resolveObject()
    {
        return $this->object;
    }

    public function resolveFieldValue($fieldName)
    {
        $argsMappedFieldName = $this->metadata->getMappedFieldName($fieldName);

        return $this->hydrator->extractProperty($this->object, $argsMappedFieldName);
    }

    public function resolveClass($class)
    {
        return $this->classResolver ? $this->classResolver->resolve($class) : new $class; 
    }

    public function resolveSourceResult($id)
    {
        $sourceMetadata = $this->metadata->findSourceById($id);
        
        return $this->hydrator->findFromSource($sourceMetadata);
    }

    private function resolveService($serviceId)
    {
        if ($this->classResolver) {
            return $this->classResolver->resolve($serviceId);
        } 
        
        throw new ObjectMappingException(sprintf('Cannot resolve id "%s". No resolver is available.', substr($serviceId, self::$serviceMarkerSize)));
    }
}
