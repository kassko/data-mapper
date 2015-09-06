<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadata;
use Kassko\DataMapper\Configuration\Configuration;

/**
 * Class InnerYamlLoaderTest
 *
 * @author Alexey Rusnak
 */
class InnerYamlLoaderTest extends InnerPhpLoaderTest
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
            'loadInnerYamlMetadata'
        );
        $loader = new ClassMetadataLoader\InnerYamlLoader();
        $delegatingLoaderMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\DelegatingLoader'
        )->disableOriginalConstructor()->getMock();

        return $loader->loadClassMetadata(
            new ClassMetadata\ClassMetadata($fullClassName),
            $loadingCriteria,
            new Configuration(),
            $delegatingLoaderMock
        );
    }
}
