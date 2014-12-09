<?php

namespace Kassko\DataMapper\ClassMetadata;

/**
 * Broadcast events during data access.
 *
 * @author kko
 */
class Events
{
    const POST_LOAD_METADATA = 'post.load_metadata';

    private function __construct() {}
    private function __clone() {}
}
