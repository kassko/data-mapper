<?php
namespace Kassko\DataMapper;

use Kassko\ClassResolver\ClassResolverInterface;
use Kassko\DataMapper\ClassMetadata\ClassMetadata;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Hydrator\AbstractHydrator;

/**
* MethodArgumentResolver
*
* @author kko
*/
class MethodArgumentResolver
{
    private static $serviceMarker = '@';

    private $object;
    private $hydrator;
    private $metadata;
    private $classResolver;

    public function __construct($object, AbstractHydrator $hydrator, ClassMetadata $metadata, ClassResolverInterface $classResolver = null)
    {
        $this->object = $object;
        $this->hydrator = $hydrator;
        $this->metadata = $metadata;
        $this->classResolver = $classResolver;
    }

    public function handle($arg)
    {
        if ('##this' === $arg) {
            return $this->object; 
        } 

        if ('#' === $arg[0]) {
            return $this->resolveFieldValue(substr($arg, 1));
        } 

        if ('@' === $arg[0]) {
            return $this->resolveServiceFromExpression($arg);
        }

        throw new UnexpectedExpressionException($arg);
    }

    public function resolveObject()
    {
        return $this->object;
    }

    public function resolveFieldValue($fieldName)
    {
        $argsMappedFieldName = $this->metadata->getMappedFieldName($fieldName);
        return $this->extractProperty($this->object, $argsMappedFieldName);
    }

    public function resolveService($serviceId)
    {
        $this->resolveServiceFromExpression(self::$serviceMarker . $serviceId);
    }

    public function resolveServiceFromExpression($serviceId)
    {
        if ($this->classResolver) {
            $serviceId = $this->classResolver->resolve(self::$serviceMarker . $serviceId);
        } else {
            throw new ObjectMappingException(sprintf('Cannot resolve id "%s". No resolver is available.', substr($serviceId, 1)));
        }
    }
}
