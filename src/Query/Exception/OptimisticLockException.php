<?php

namespace Kassko\DataMapper\Query\Exception;

use Exception;

/**
* Exception thrown when a concurrent update.
*
* @author kko
*/
class OptimisticLockException extends Exception
{
    /**
     * @var object|null
     */
    private $entity;

    /**
     * @param string $msg
     * @param object $entity
     */
    public function __construct($msg, $entity)
    {
        parent::__construct($msg);

        $this->entity = $entity;
    }

    public static function versionMismatch($entity, $versionRef, $version)
    {
        return new self(
            sprintf(
                'A concurrency was detected during an update. Version "%s" expected and got "%s".',
                $versionRef,
                $version
            ),
            $entity
        );
    }

    /**
     * Gets the entity that caused the exception.
     *
     * @return object|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
