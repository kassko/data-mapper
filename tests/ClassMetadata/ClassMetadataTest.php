<?php
namespace Kassko\DataMapperTest\ClassMetadata;

use Kassko\DataMapper\ClassMetadata;

/**
 * Class ClassMetadataTest
 * 
 * @author Alexey Rusnak
 */
class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata\ClassMetadata
     */
    protected $classMetadata;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->classMetadata = new ClassMetadata\ClassMetadata('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass');
    }

    /**
     * @test
     */
    public function getNameValidateResult()
    {
        $result = $this->classMetadata->getName();

        $this->assertEquals('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass', $result);
    }

    /**
     * @test
     */
    public function getReflectionClassValidateResult()
    {
        /**
         * @var \ReflectionClass $result
         */
        $result = $this->classMetadata->getReflectionClass();

        $this->assertInstanceOf('\ReflectionClass', $result);
        $this->assertEquals('Kassko\DataMapperTest\ClassMetadata\Fixture\SampleClass', $result->getName());
    }

    /**
     * @test
     */
    public function getFieldExclusionPolicyValidateDefaultValue()
    {
        $result = $this->classMetadata->getFieldExclusionPolicy();

        $this->assertEquals('include_all', $result);
    }

    /**
     * @test
     */
    public function setFieldExclusionPolicyValidateResult()
    {
        $exclusionPolicy = 'none';
        $result = $this->classMetadata->setFieldExclusionPolicy($exclusionPolicy);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($exclusionPolicy, $this->classMetadata->getFieldExclusionPolicy());
    }

    /**
     * @test
     */
    public function getRepositoryClassDefaultValue()
    {
        $this->assertNull($this->classMetadata->getRepositoryClass());
    }

    /**
     * @test
     */
    public function setRepositoryClassValidateResult()
    {
        $repositoryClass = '\DateTime';
        $result = $this->classMetadata->setRepositoryClass($repositoryClass);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($repositoryClass, $this->classMetadata->getRepositoryClass());
    }

    /**
     * @test
     */
    public function getObjectReadDateFormatValidateDefaultValue()
    {
        $this->assertNull($this->classMetadata->getObjectReadDateFormat());
    }

    /**
     * @test
     */
    public function setObjectReadDateFormatValidateResult()
    {
        $dateFormat = 'Y-m-d';
        $result = $this->classMetadata->setObjectReadDateFormat($dateFormat);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($dateFormat, $this->classMetadata->getObjectReadDateFormat());
    }

    /**
     * @test
     */
    public function getObjectWriteDateFormatValidateDefaultValue()
    {
        $this->assertNull($this->classMetadata->getObjectWriteDateFormat());
    }

    /**
     * @test
     */
    public function setObjectWriteDateFormatValidateResult()
    {
        $dateFormat = 'Y-m-d';
        $result = $this->classMetadata->setObjectWriteDateFormat($dateFormat);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($dateFormat, $this->classMetadata->getObjectWriteDateFormat());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setOriginalFieldNames() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setOriginalFieldNamesValidateTypeHinting()
    {
        $this->classMetadata->setOriginalFieldNames($this);
    }

    /**
     * @test
     */
    public function getOriginalFieldNamesValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getOriginalFieldNames());
    }

    /**
     * @test
     */
    public function setOriginalFieldNamesValidateResult()
    {
        $fieldNames = array('field1' => 'mappedField1', 'field2' => 'mappedField2');
        $result = $this->classMetadata->setOriginalFieldNames($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getOriginalFieldNames());
    }

    /**
     * @test
     */
    public function getMappedDateFieldNamesValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getMappedDateFieldNames());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setMappedDateFieldNames() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setMappedDateFieldNamesValidateTypeHinting()
    {
        $this->classMetadata->setMappedDateFieldNames($this);
    }

    /**
     * @test
     */
    public function setMappedDateFieldNamesValidateResult()
    {
        $fieldNames = array('dateField1' => 'mappedDateField1', 'dateField2' => 'mappedDateField2');
        $result = $this->classMetadata->setMappedDateFieldNames($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getMappedDateFieldNames());
    }

    /**
     * @test
     */
    public function getMappedFieldNamesValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getMappedFieldNames());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setMappedFieldNames() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setMappedFieldNamesValidateTypeHinting()
    {
        $this->classMetadata->setMappedFieldNames($this);
    }

    /**
     * @test
     */
    public function setMappedFieldNamesValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedField1', 'mappedField2' => 'mappedField2');
        $result = $this->classMetadata->setMappedFieldNames($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getMappedFieldNames());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setFieldsDataByKey() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setFieldsDataByKeyValidateTypeHinting()
    {
        $this->classMetadata->setFieldsDataByKey($this);
    }

    /**
     * @test
     */
    public function getFieldsDataByKeyValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getFieldsDataByKey());
    }

    /**
     * @test
     */
    public function setFieldsDataByKeyValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedFieldData1', 'mappedField2' => 'mappedFieldData2');
        $result = $this->classMetadata->setFieldsDataByKey($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getFieldsDataByKey());
    }

    /**
     * @test
     */
    public function getMappedIdFieldNameValidateDefaultValue()
    {
        $this->assertNull($this->classMetadata->getMappedIdFieldName());
    }

    /**
     * @test
     */
    public function setMappedIdFieldNameValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedFieldData1', 'mappedField2' => 'mappedFieldData2');
        $result = $this->classMetadata->setMappedIdFieldName($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getMappedIdFieldName());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setMappedIdCompositePartFieldName() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setMappedIdCompositePartFieldNameValidateTypeHinting()
    {
        $this->classMetadata->setMappedIdCompositePartFieldName($this);
    }

    /**
     * @test
     */
    public function getMappedIdCompositePartFieldNameValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getFieldsDataByKey());
    }

    /**
     * @test
     */
    public function setMappedIdCompositePartFieldNameValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedFieldData1', 'mappedField2' => 'mappedFieldData2');
        $result = $this->classMetadata->setMappedIdCompositePartFieldName($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getMappedIdCompositePartFieldName());
    }

    /**
     * @test
     */
    public function getMappedVersionFieldNameValidateDefaultValue()
    {
        $this->assertNull($this->classMetadata->getMappedVersionFieldName());
    }

    /**
     * @test
     */
    public function setMappedVersionFieldNameValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedFieldData1', 'mappedField2' => 'mappedFieldData2');
        $result = $this->classMetadata->setMappedVersionFieldName($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getMappedVersionFieldName());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setToOriginal() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setToOriginalValidateTypeHinting()
    {
        $this->classMetadata->setToOriginal($this);
    }

    /**
     * @test
     */
    public function getToOriginalValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getToOriginal());
    }

    /**
     * @test
     */
    public function setToOriginalValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedFieldData1', 'mappedField2' => 'mappedFieldData2');
        $result = $this->classMetadata->setToOriginal($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getToOriginal());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setToMapped() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setToMappedValidateTypeHinting()
    {
        $this->classMetadata->setToMapped($this);
    }

    /**
     * @test
     */
    public function getToMappedValidateDefaultValue()
    {
        $this->assertEquals(array(), $this->classMetadata->getToMapped());
    }

    /**
     * @test
     */
    public function setToMappedValidateResult()
    {
        $fieldNames = array('mappedField1' => 'mappedFieldData1', 'mappedField2' => 'mappedFieldData2');
        $result = $this->classMetadata->setToMapped($fieldNames);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($fieldNames, $this->classMetadata->getToMapped());
    }

    /**
     * @test
     */
    public function isPropertyAccessStrategyEnabledValidateDefaultValue()
    {
        $this->assertNull($this->classMetadata->isPropertyAccessStrategyEnabled());
    }

    /**
     * @test
     */
    public function setPropertyAccessStrategyEnabledValidateResult()
    {
        $result = $this->classMetadata->setPropertyAccessStrategyEnabled(true);

        $this->assertSame($this->classMetadata, $result);
        $this->assertTrue($this->classMetadata->isPropertyAccessStrategyEnabled());
    }

    /**
     * @test
     */
    public function getMetadataExtensionClassByMappedFieldWithFieldMappingExtensionClass()
    {
        $fieldName = 'fieldName';
        $fieldsDataByKey = array(
            $fieldName => array('field' => array('fieldMappingExtensionClass' => 'testExtensionClass'))
        );
        $this->classMetadata->setFieldsDataByKey($fieldsDataByKey);
        $result = $this->classMetadata->getMetadataExtensionClassByMappedField($fieldName);

        $this->assertEquals('testExtensionClass', $result);
    }

    /**
     * @test
     */
    public function getMetadataExtensionClassByMappedFieldWithPropertyMetadataExtensionClass()
    {
        $fieldName = 'fieldName';
        $fieldsDataByKey = array(
            $fieldName => array('field' => 'fieldValue')
        );
        $this->classMetadata->setFieldsDataByKey($fieldsDataByKey);
        $propertyMetadataExtensionClass = '\TestPropertyMetadataExtensionClass';
        $this->classMetadata->setPropertyMetadataExtensionClass($propertyMetadataExtensionClass);
        $result = $this->classMetadata->getMetadataExtensionClassByMappedField($fieldName);

        $this->assertEquals($propertyMetadataExtensionClass, $result);
    }

    /**
     * @test
     */
    public function getMetadataExtensionClassByMappedFieldWithoutAll()
    {
        $fieldName = 'fieldName';
        $result = $this->classMetadata->getMetadataExtensionClassByMappedField($fieldName);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function getPropertyMetadataExtensionClassValidateDefaultValue()
    {
        $result = $this->classMetadata->getPropertyMetadataExtensionClass();

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function setPropertyMetadataExtensionClassValidateResult()
    {
        $propertyMetadataExtensionClass = '\DateTime';
        $result = $this->classMetadata->setPropertyMetadataExtensionClass($propertyMetadataExtensionClass);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($propertyMetadataExtensionClass, $this->classMetadata->getPropertyMetadataExtensionClass());
    }

    /**
     * @test
     */
    public function getClassMetadataExtensionClassValidateDefaultValue()
    {
        $result = $this->classMetadata->getClassMetadataExtensionClass();

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function setClassMetadataExtensionClassValidateResult()
    {
        $classMetadataExtensionClass = '\DateTime';
        $result = $this->classMetadata->setClassMetadataExtensionClass($classMetadataExtensionClass);

        $this->assertSame($this->classMetadata, $result);
        $this->assertEquals($classMetadataExtensionClass, $this->classMetadata->getClassMetadataExtensionClass());
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setValueObjects() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setValueObjectsValidateTypeHinting()
    {
        $this->classMetadata->setValueObjects($this);
    }

    /**
     * @test
     */
    public function setValueObjectsValidateResult()
    {
        $valueObjects = array('test' => true);
        $this->classMetadata->setValueObjects($valueObjects);

        foreach ($valueObjects as $name => $value) {
            $this->assertTrue($this->classMetadata->isValueObject($name));
        }
        $this->assertFalse($this->classMetadata->isValueObject('someInvalidValue'));
    }

    /**
     * @test
     */
    public function getFieldsWithValueObjectsValidateResult()
    {
        $valueObjects = array('test' => true, 'test1' => true);
        $this->classMetadata->setValueObjects($valueObjects);

        $this->assertEquals(array_keys($valueObjects), $this->classMetadata->getFieldsWithValueObjects());
    }

    /**
     * @test
     * @dataProvider getValueObjectInfoDataProvider
     * @param array $valueObjects
     * @param array $expectedResult
     */
    public function getValueObjectInfoValidateResult($valueObjects, $expectedResult)
    {
        $this->classMetadata->setValueObjects($valueObjects);
        $result = $this->classMetadata->getValueObjectInfo('testValueObject');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getValueObjectInfoDataProvider()
    {
        return array(
            array(
                array(
                    'testValueObject' => array('mappingResourcePath' => '/tmp/test',
                                               'mappingResourceName' => 'testResourceName',
                                               'class' => 'testClassName',
                                               'mappingResourceType' => 'testResourceType')),
                array('testClassName', '/tmp/test/testResourceName', 'testResourceType')
            ),
            array(
                array(
                    'testValueObject' => array('mappingResourceName' => 'testResourceName',
                                               'class' => 'testClassName',
                                               'mappingResourceType' => 'testResourceType')),
                array('testClassName', 'testResourceName', 'testResourceType')
            ),
            array(
                array(
                    'testValueObject' => array('mappingResourceName' => 'testResourceName',
                                               'class' => 'testClassName')),
                array('testClassName', 'testResourceName', null)
            ));
    }

    /**
     * @test
     * @expectedExceptionMessage Argument 1 passed to Kassko\DataMapper\ClassMetadata\ClassMetadata::setMappedTransientFieldNames() must be of the type array, object given
     * @expectedException \PHPUnit_Framework_Error
     */
    public function setMappedTransientFieldNamesValidateTypeHinting()
    {
        $this->classMetadata->setMappedTransientFieldNames($this);
    }

    /**
     * @test
     */
    public function setMappedTransientFieldNamesValidateResult()
    {
        $mappedTransientFieldNames = array('transientField' => 'transientFieldValue');
        $this->classMetadata->setMappedTransientFieldNames($mappedTransientFieldNames);

        foreach ($mappedTransientFieldNames as $name => $value) {
            $this->assertTrue($this->classMetadata->isTransient($value));
        }
        $this->assertFalse($this->classMetadata->isTransient('someInvalidValue'));
    }
}
