<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader;

use Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class LoaderResolverTest
 *
 * @author Alexey Rusnak
 */
class LoaderResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadataLoader\LoaderResolver
     */
    protected $loaderResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $loaders = array();

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loaders[] = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
        $this->loaders[] = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
        $this->loaderResolver = new ClassMetadataLoader\LoaderResolver($this->loaders);
    }

    /**
     * @test
     */
    public function instanceOfLoaderResolverInterface()
    {
        $this->assertInstanceOf(
            '\Kassko\DataMapper\ClassMetadataLoader\LoaderResolverInterface',
            $this->loaderResolver
        );
    }

    /**
     * @test
     */
    public function getLoadersValidateResult()
    {
        $this->assertEquals($this->loaders, $this->loaderResolver->getLoaders());
    }

    /**
     * @test
     */
    public function getLoadersValidateDefaultResult()
    {
        $loaderResolver = new ClassMetadataLoader\LoaderResolver();
        $this->assertEquals(array(), $loaderResolver->getLoaders());
    }

    /**
     * @test
     */
    public function setLoadersValidateReturnValue()
    {
        $this->assertSame($this->loaderResolver, $this->loaderResolver->setLoaders(array()));
    }

    /**
     * @test
     */
    public function addLoaderValidateReturnValue()
    {
        $loader = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
        $this->assertSame($this->loaderResolver, $this->loaderResolver->addLoader($loader));
    }

    /**
     * @test
     */
    public function addLoaderValidateResult()
    {
        $loader = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
        $this->loaderResolver->addLoader($loader);
        $this->assertContains($loader, $this->loaderResolver->getLoaders());
    }

    /**
     * @test
     */
    public function addLoadersValidateReturnValue()
    {
        $this->assertSame($this->loaderResolver, $this->loaderResolver->addLoaders(array()));
    }

    /**
     * @test
     * @TODO Non-obvious behaviour when merging loaders.
     */
    public function addLoadersValidateResult()
    {
        $newLoaders = array(
            123 => $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass(),
            255 => $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass()
        );
        $this->loaderResolver->addLoaders($newLoaders);

        foreach ($newLoaders as $loader) {
            $this->assertContains($loader, $this->loaderResolver->getLoaders());
        }
    }

    /**
     * @test
     */
    public function addLoadersValidateResultWithPrepend()
    {
        $newLoaders = array(
            123 => $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass(),
            255 => $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass()
        );
        $this->loaderResolver->addLoaders($newLoaders, true);

        foreach ($newLoaders as $loader) {
            $this->assertContains($loader, $this->loaderResolver->getLoaders());
        }
    }

    /**
     * @test
     */
    public function addLoadersValidateMergeArrays()
    {
        $newLoaders = array(
            $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass(),
            $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass()
        );
        $this->loaderResolver->addLoaders($newLoaders);

        foreach ($newLoaders as $loader) {
            $this->assertNotContains($loader, $this->loaderResolver->getLoaders());
        }
    }

    /**
     * @test
     */
    public function addLoadersValidateMergeArraysWithPrepend()
    {
        $newLoaders = array(
            $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass(),
            $this->getMockBuilder(
                '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
            )->getMockForAbstractClass()
        );
        $this->loaderResolver->addLoaders($newLoaders, true);

        foreach ($newLoaders as $loader) {
            $this->assertContains($loader, $this->loaderResolver->getLoaders());
        }
    }

    /**
     * @test
     */
    public function setLoadersValidateResult()
    {
        $loader = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
        $loaders = array($loader);
        $this->loaderResolver->setLoaders($loaders);
        $this->assertSame($loaders, $this->loaderResolver->getLoaders());
    }

    /**
     * @test
     */
    public function resolveLoaderValidateDefaultResult()
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )
        ->disableOriginalConstructor()
        ->getMock();
        foreach ($this->loaders as $loader) {
            $loader->expects($this->once())
                ->method('supports')
                ->with($loadingCriteriaMock)
                ->willReturn(false);
        }

        $this->assertFalse($this->loaderResolver->resolveLoader($loadingCriteriaMock));
    }

    /**
     * @test
     */
    public function resolveLoaderValidateResult()
    {
        $loadingCriteriaMock = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\LoadingCriteria'
        )
        ->disableOriginalConstructor()
        ->getMock();
        foreach ($this->loaders as $loader) {
            $loader->expects($this->once())
                   ->method('supports')
                   ->with($loadingCriteriaMock)
                   ->willReturn(false);
        }
        $loader = $this->getMockBuilder(
            '\Kassko\DataMapper\ClassMetadataLoader\AbstractLoader'
        )->getMockForAbstractClass();
        $loader->expects($this->once())
            ->method('supports')
            ->with($loadingCriteriaMock)
            ->willReturn(true);
        $this->loaderResolver->addLoader($loader);

        $this->assertEquals($loader, $this->loaderResolver->resolveLoader($loadingCriteriaMock));
    }
}
