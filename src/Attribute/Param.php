<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Attribute;

use Attribute;

/**
 * Defines a parameter value for constructor, getter, or setter arguments.
 * 
 * The value can be a static value or an expression that will be resolved at runtime.
 * 
 * Usage in constructor:
 * ```php
 * class Person
 * {
 *     public function __construct(
 *         #[Param(value: "expr(context('user_id'))")] int $id
 *     ) {
 *         $this->id = $id;
 *     }
 * }
 * ```
 * 
 * Usage in getter:
 * ```php
 * public function getName(
 *     #[Param(value: "expr(service('nameService'))")] $nameService
 * ): ?string {
 *     $this->loadProperty('name');
 *     return $this->name;
 * }
 * ```
 * 
 * Usage in setter (from 2nd parameter):
 * ```php
 * public function setEmail(
 *     $email,  // First parameter: regular, no Param allowed
 *     #[Param(value: "expr(context('enforcedEmail'))")] $enforcedEmail = null
 * ): void {
 *     $this->email = $enforcedEmail ?: $email;
 * }
 * ```
 * 
 * Rules:
 * - Constructor: All parameters MUST have Param attribute. Property references (#id, property('id'), ##object) are forbidden.
 * - Getter: Parameters with Param are injected; regular parameters are forbidden.
 * - Setter: First parameter MUST NOT have Param; additional parameters MUST have Param.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Param
{
    /**
     * @param string $value The value or expression to inject. Can be:
     *                      - A static string/value: "bob"
     *                      - A context expression: "expr(context('key'))"
     *                      - A service expression: "expr(service('serviceId'))"
     *                      - A source expression: "expr(source('sourceId'))"
     *                      Note: For constructor params, property references are forbidden.
     */
    public function __construct(
        public readonly string $value
    ) {
    }
}
