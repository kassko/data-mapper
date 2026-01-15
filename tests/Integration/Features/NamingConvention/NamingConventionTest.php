<?php

declare(strict_types=1);

namespace Kassko\DataMapper\Tests\Integration\Features\NamingConvention;

use Kassko\DataMapper\Attribute\Property;
use Kassko\DataMapper\DataMapperBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Test automatic naming convention detection.
 * 
 * This tests the fix for the bug where recursive hydration failed
 * when raw data keys used snake_case but properties used camelCase.
 */
class NamingConventionTest extends TestCase
{
    /**
     * Test that snake_case keys in raw data are automatically mapped to camelCase properties.
     */
    public function testSnakeCaseToCamelCaseAutoMapping(): void
    {
        $dataMapper = (new DataMapperBuilder())->build();
        $hydrator = $dataMapper->getHydrator();

        // Raw data with snake_case keys
        $rawData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john.doe@example.com',
        ];

        $person = $hydrator->hydrate(PersonWithCamelCaseProps::class, $rawData);

        $this->assertEquals('John', $person->firstName);
        $this->assertEquals('Doe', $person->lastName);
        $this->assertEquals('john.doe@example.com', $person->emailAddress);
    }

    /**
     * Test that camelCase keys in raw data are automatically mapped to snake_case properties.
     */
    public function testCamelCaseToSnakeCaseAutoMapping(): void
    {
        $dataMapper = (new DataMapperBuilder())->build();
        $hydrator = $dataMapper->getHydrator();

        // Raw data with camelCase keys
        $rawData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'emailAddress' => 'jane.smith@example.com',
        ];

        $person = $hydrator->hydrate(PersonWithSnakeCaseProps::class, $rawData);

        $this->assertEquals('Jane', $person->first_name);
        $this->assertEquals('Smith', $person->last_name);
        $this->assertEquals('jane.smith@example.com', $person->email_address);
    }

    /**
     * Test recursive hydration with nested objects using different naming conventions.
     */
    public function testRecursiveHydrationWithNamingConvention(): void
    {
        $dataMapper = (new DataMapperBuilder())->build();
        $hydrator = $dataMapper->getHydrator();

        // Raw data with snake_case keys and nested array
        $rawData = [
            'company_name' => 'ACME Corp',
            'employees' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'job_title' => 'Developer',
                ],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'job_title' => 'Manager',
                ],
            ],
        ];

        $company = $hydrator->hydrate(CompanyWithEmployees::class, $rawData);

        $this->assertEquals('ACME Corp', $company->companyName);
        $this->assertCount(2, $company->employees);
        
        $this->assertEquals('John', $company->employees[0]->firstName);
        $this->assertEquals('Doe', $company->employees[0]->lastName);
        $this->assertEquals('Developer', $company->employees[0]->jobTitle);
        
        $this->assertEquals('Jane', $company->employees[1]->firstName);
        $this->assertEquals('Smith', $company->employees[1]->lastName);
        $this->assertEquals('Manager', $company->employees[1]->jobTitle);
    }

    /**
     * Test that explicit sourceField still takes precedence over auto-detection.
     */
    public function testExplicitSourceFieldTakesPrecedence(): void
    {
        $dataMapper = (new DataMapperBuilder())->build();
        $hydrator = $dataMapper->getHydrator();

        // Raw data with custom key names
        $rawData = [
            'given_name' => 'John',
            'family_name' => 'Doe',
        ];

        $person = $hydrator->hydrate(PersonWithExplicitSourceField::class, $rawData);

        $this->assertEquals('John', $person->firstName);
        $this->assertEquals('Doe', $person->lastName);
    }

    /**
     * Test that exact match takes precedence over naming convention conversion.
     */
    public function testExactMatchTakesPrecedence(): void
    {
        $dataMapper = (new DataMapperBuilder())->build();
        $hydrator = $dataMapper->getHydrator();

        // Raw data with exact match for property name
        $rawData = [
            'firstName' => 'John',  // Exact match
            'first_name' => 'Not John',  // Snake case version also exists
            'lastName' => 'Doe',
        ];

        $person = $hydrator->hydrate(PersonWithCamelCaseProps::class, $rawData);

        // Should use exact match 'firstName', not 'first_name'
        $this->assertEquals('John', $person->firstName);
        $this->assertEquals('Doe', $person->lastName);
    }
}

/**
 * Model with camelCase properties, to be hydrated from snake_case raw data.
 */
class PersonWithCamelCaseProps
{
    public string $firstName = '';
    public string $lastName = '';
    public string $emailAddress = '';
}

/**
 * Model with snake_case properties, to be hydrated from camelCase raw data.
 */
class PersonWithSnakeCaseProps
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email_address = '';
}

/**
 * Company model with nested employees array using camelCase properties.
 */
class CompanyWithEmployees
{
    public string $companyName = '';

    #[Property(class: Employee::class)]
    public array $employees = [];
}

/**
 * Employee model with camelCase properties.
 */
class Employee
{
    public string $firstName = '';
    public string $lastName = '';
    public string $jobTitle = '';
}

/**
 * Model with explicit sourceField mapping.
 */
class PersonWithExplicitSourceField
{
    #[Property(sourceField: 'given_name')]
    public string $firstName = '';

    #[Property(sourceField: 'family_name')]
    public string $lastName = '';
}
