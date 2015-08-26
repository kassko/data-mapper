<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Class InnerPhpLoaderTest
 *
 * @author Alexey Rusnak
 */
class InnerPhpLoaderTest extends ArrayLoaderTest
{
    /**
     * @param string $className
     * @return ClassMetadata\ClassMetadata
     */
    public function loadMetadata($className)
    {
        $fullClassName = $this->getMetadataClassName($className);
        $loadingCriteria = ClassMetadataLoader\LoadingCriteria::create(
            '',
            '',
            $fullClassName,
            'loadInnerPhpMetadata'
        );
        $loader = new ClassMetadataLoader\InnerPhpLoader();
        return $loader->loadClassMetadata(
            new ClassMetadata\ClassMetadata($fullClassName),
            $loadingCriteria,
            new Configuration(),
            $loader
        );
    }
}
