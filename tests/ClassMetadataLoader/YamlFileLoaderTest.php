<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;
use Kassko\DataMapper\ClassMetadata;

/**
 * Class YamlFileLoaderTest
 *
 * @author Alexey Rusnak
 */
class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadataLoader\YamlFileLoader
     */
    protected $loader;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loader = new ClassMetadataLoader\YamlFileLoader();
    }

    /**
     * @test
     * @dataProvider supportsValidateResultAndCallsDataProvider
     * @param string $resourceType
     * @param bool $expectedResult
     */
    public function supportsValidateResultAndCalls($resourceType, $expectedResult)
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            'Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )->disableOriginalConstructor()->getMock();
        $loadingCriteriaMock->expects($this->once())
            ->method('getResourceType')
            ->willReturn($resourceType);

        $this->assertEquals($expectedResult, $this->loader->supports($loadingCriteriaMock));
    }

    /**
     * @return array
     */
    public function supportsValidateResultAndCallsDataProvider()
    {
        return array(
            array('php_file', false),
            array('inner_yaml', false),
            array('inner_php', false),
            array('yaml_file', true)
        );
    }
}
