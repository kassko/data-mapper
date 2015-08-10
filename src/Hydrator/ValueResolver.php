<?php
namespace Kassko\DataMapper\Hydrator;

use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Hydrator\Exception\NotResolvableValueException;
use Kassko\DataMapper\Hydrator\Hydrator;

/**
* ValueResolver
*
* @author kko
*/
class ValueResolver
{
    private static $serviceMarker = '@';
    private static $serviceMarkerSize = 1;
    private static $fieldMarker = '#';
    private static $fieldMarkerSize = 1;
    
    private $object;//TODO: check if this attribute is still usefull an remove it if function resolveObject() can replaces it.
    private $hydrator;
    private $metadata;
    private $classResolver;

    public function __construct(Hydrator $hydrator, ClassMetadata $metadata, ClassResolverInterface $classResolver = null)
    {        
        $this->hydrator = $hydrator;
        $this->metadata = $metadata;
        $this->classResolver = $classResolver;
    }

    public function handle($value, $object)
    {
        $this->object = $object;

        if ('##this' === $value) {
            return $this->object; 
        } 

        if ('##data' === $value) {
            return $this->resolveRawData(); 
        } 

        if (self::$fieldMarker === $value[0]) {
            return $this->resolveFieldValue(substr($value, self::$fieldMarkerSize));
        } 

        if (self::$serviceMarker === $value[0]) {
            return $this->resolveService($value);
        }

        throw new NotResolvableValueException($value);
    }

    public function resolveRawData()
    {
        return $this->hydrator->getCurrentRawData();
    }

    public function resolveObject()
    {
        return $this->hydrator->getCurrentObject();
    }

    public function resolveFieldValue($fieldName)
    {
        $fieldToResolve = $this->metadata->getMappedFieldName($fieldName);

        return $this->hydrator->extractProperty($this->object, $fieldToResolve);
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

    public function resolveVariable($variableName)
    {
        return $this->hydrator->getCurrentConfigVariableByName($variableName);
    }

    protected function resolveService($serviceId)
    {
        if ($this->classResolver) {
            return $this->classResolver->resolve($serviceId);
        } 
        
        throw new ObjectMappingException(sprintf('Cannot resolve id "%s". No resolver is available.', substr($serviceId, self::$serviceMarkerSize)));
    }
}
