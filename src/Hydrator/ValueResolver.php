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
    private $serviceMarker = '@';
    private $serviceMarkerSize = 1;
    private $fieldMarker = '#';
    private $fieldMarkerSize = 1;
    private $directFieldMarker = '#!';
     private $directFieldMarkerSize = 2;
    
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

        if ('##parent' === $value) {
            return $this->resolveParentObject(); 
        }  

        if ('##data' === $value) {
            return $this->resolveRawData(); 
        } 

        if ($this->directFieldMarker === substr($value, 0, $this->directFieldMarkerSize)) {
            return $this->resolveFieldValue(substr($value, $this->directFieldMarkerSize), true);
        } 

        if ($this->fieldMarker === $value[0]) {
            return $this->resolveFieldValue(substr($value, $this->fieldMarkerSize), false);
        } 

        if ($this->serviceMarker === $value[0]) {
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

    public function resolveParentObject()
    {
        return $this->hydrator->getParentOfObjectCurrentlyHydrated();
    }

    public function resolveFieldValue($fieldName, $bypassLoading)
    {
        $fieldToResolve = $this->metadata->getMappedFieldName($fieldName);

        return $this->hydrator->extractProperty($this->object, $fieldToResolve, null, $bypassLoading);
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
        
        throw new ObjectMappingException(sprintf('Cannot resolve id "%s". No resolver is available.', substr($serviceId, $this->serviceMarkerSize)));
    }
}
