<?php

namespace Kassko\DataAccess\Listener;

/**
 * Broadcast events during data access.
 *
 * @author kko
 */
class Events
{
	const OBJECT_PRE_CREATE = 'object.pre.create';
	const OBJECT_PRE_UPDATE = 'object.pre.update';
	const OBJECT_PRE_DELETE = 'object.pre.delete';

	const OBJECT_POST_CREATE = 'object.post.create';
	const OBJECT_POST_UPDATE = 'object.post.update';
	const OBJECT_POST_DELETE = 'object.post.delete';
	const OBJECT_POST_LOAD = 'object.post.load';
	const OBJECT_POST_LOAD_LIST = 'object.post.load.list';

    private function __construct() {}
    private function __clone() {}
}