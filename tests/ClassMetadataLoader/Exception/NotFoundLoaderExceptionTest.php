<?php
namespace Kassko\DataMapperTest\ClassMetadataLoader\Exception;

use Kassko\DataMapperTest\ClassMetadataLoader\Fixture;
use Kassko\DataMapper\ClassMetadataLoader\Exception\NotFoundLoaderException;

/**
 * Class NotFoundLoaderExceptionTest
 * @package Kassko\DataMapperTest\ClassMetadataLoader\Exception
 * @author Alexey Rusnak
 */
class NotFoundLoaderExceptionTest extends \PHPUnit_Framework_TestCase
{
        /**
         * @var NotFoundLoaderException
         */
        protected $exception;

        /**
         * @var Fixture\LoadingCriteria|\PHPUnit_Framework_MockObject_MockObject
         */
        protected $loadingCriteriaMock;

        /**
         * @return void
         */
        public function setUp()
        {
                $this->loadingCriteriaMock = $this->getMockBuilder(
                        'Kassko\DataMapperTest\ClassMetadataLoader\Fixture\LoadingCriteria'
                )->getMock();

                $resourcePath = 'testResourcePath';
                $resourceType = 'testResourceType';
                $resourceClass = 'testResourceClass';
                $resourceMethod = 'testResourceMethod';
                $this->loadingCriteriaMock->expects($this->once())
                                                                    ->method('getResourcePath')
                                                                    ->willReturn($resourcePath);
                $this->loadingCriteriaMock->expects($this->once())
                                                                    ->method('getResourceType')
                                                                    ->willReturn($resourceType);
                $this->loadingCriteriaMock->expects($this->once())
                                                                    ->method('getResourceClass')
                                                                    ->willReturn($resourceClass);
                $this->loadingCriteriaMock->expects($this->once())
                                                                    ->method('getResourceMethod')
                                                                    ->willReturn($resourceMethod);

                $this->exception = new NotFoundLoaderException($this->loadingCriteriaMock);
        }

        /**
         * @test
         */
        public function instanceOfRuntimeException()
        {
                $this->assertInstanceOf('\RuntimeException', $this->exception);
        }

        /**
         * @test
         */
        public function constructorValidateCalls()
        {
                $this->assertEquals(
                        'No loader found or no loader satisfies the following criteria: [resourcePath="testResourcePath"] - ' .
                        '[resourceType="testResourceType"] - [resourceClass="testResourceClass"] - [resourceMethod="testResourceMethod"]',
                        $this->exception->getMessage()
                );
        }
}
