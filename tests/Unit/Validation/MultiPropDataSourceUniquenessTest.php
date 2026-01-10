<?php

declare(strict_types=1);

/*
 * This file is part of Data Mapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Tests\Unit\Validation;

use Kassko\DataMapper\Tests\TestHelpers\LocalFixtureAutoloadTrait;
use Kassko\DataMapper\Validation\MetadataValidator;
use Kassko\Sample\MultiPropUniqueness\DuplicateDataSourceRefOnProperties;
use Kassko\Sample\MultiPropUniqueness\DuplicateMultiPropOnProperties;
use Kassko\Sample\MultiPropUniqueness\MixedDuplicateViolation;
use Kassko\Sample\MultiPropUniqueness\ValidMultipleSources;
use Kassko\Sample\MultiPropUniqueness\ValidSingleSource;
use Kassko\Sample\MultiPropUniqueness\ValidWithCrossHydration;
use PHPUnit\Framework\TestCase;

/**
 * Tests for MultiPropDataSource uniqueness validation.
 * 
 * Each MultiPropDataSource id must be used only ONCE per class.
 * Other properties are hydrated via cross-hydration if the data contains matching keys.
 */
class MultiPropDataSourceUniquenessTest extends TestCase
{
    use LocalFixtureAutoloadTrait;

    private MetadataValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new MetadataValidator();
    }

    /**
     * @test
     */
    public function validSingleSourcePassesValidation(): void
    {
        $result = $this->validator->validateClass(ValidSingleSource::class);
        
        $this->assertTrue($result->isValid(), 'Valid single source should pass validation. Errors: ' . implode('; ', $result->errors));
        $this->assertEmpty($result->errors);
    }

    /**
     * @test
     */
    public function validMultipleSourcesWithDifferentIdsPassesValidation(): void
    {
        $result = $this->validator->validateClass(ValidMultipleSources::class);
        
        $this->assertTrue($result->isValid(), 'Multiple sources with different ids should pass validation. Errors: ' . implode('; ', $result->errors));
        $this->assertEmpty($result->errors);
    }

    /**
     * @test
     */
    public function validCrossHydrationPassesValidation(): void
    {
        $result = $this->validator->validateClass(ValidWithCrossHydration::class);
        
        $this->assertTrue($result->isValid(), 'Cross-hydration pattern should pass validation. Errors: ' . implode('; ', $result->errors));
        $this->assertEmpty($result->errors);
    }

    /**
     * @test
     */
    public function duplicateMultiPropDataSourceOnPropertiesFailsValidation(): void
    {
        $result = $this->validator->validateClass(DuplicateMultiPropOnProperties::class);
        
        $this->assertFalse($result->isValid(), 'Duplicate MultiPropDataSource on properties should fail validation');
        
        $errors = $result->errors;
        $this->assertCount(1, $errors);
        $this->assertStringContainsString("MultiPropDataSource with id 'personData'", $errors[0]);
        $this->assertStringContainsString("is declared on multiple properties", $errors[0]);
    }

    /**
     * @test
     */
    public function duplicateDataSourceRefToSameMultiPropFailsValidation(): void
    {
        $result = $this->validator->validateClass(DuplicateDataSourceRefOnProperties::class);
        
        $this->assertFalse($result->isValid(), 'Duplicate DataSourceRef to same MultiPropDataSource should fail validation');
        
        $errors = $result->errors;
        $this->assertCount(1, $errors);
        $this->assertStringContainsString("Multiple properties reference the same MultiPropDataSource id", $errors[0]);
    }

    /**
     * @test
     */
    public function mixedViolationsReportMultipleErrors(): void
    {
        $result = $this->validator->validateClass(MixedDuplicateViolation::class);
        
        $this->assertFalse($result->isValid(), 'Mixed violations should fail validation');
        
        $errors = $result->errors;
        $this->assertGreaterThanOrEqual(1, count($errors), 'Should report at least one error');
    }
}
