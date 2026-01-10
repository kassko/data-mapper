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

namespace Kassko\Sample\MultiPropUniqueness;

/**
 * Mock data source for testing MultiPropDataSource uniqueness validation.
 */
class PersonDataSource
{
    public function getPersonData(int $id): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
        ];
    }

    public function getBasicInfo(int $id): array
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];
    }

    public function getContactInfo(int $id): array
    {
        return [
            'email' => 'john.doe@example.com',
            'phone' => '+1-555-1234',
        ];
    }

    public function getFirstName(int $id): array
    {
        return [
            'firstName' => 'John',
        ];
    }

    public function getLastName(int $id): array
    {
        return [
            'lastName' => 'Doe',
        ];
    }

    public function getUserData(int $id): array
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
        ];
    }
}
