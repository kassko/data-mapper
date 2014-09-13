<?php

namespace Kassko\DataAccess\Registry;

use Kassko\DataAccess\LazyLoader\LazyLoaderFactoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Registry
 *
 * @author kko
 */
final class Registry
{
    /**
     * Lazy loader factory
     * @var LazyLoaderFactoryInterface
     */
    private $lazyLoaderFactory;

    /**
     * Logger
     * @var LoggerInterface
     */
    private $logger;

    public static function getInstance()
    {
        static $instance;

        if (null === $instance) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * Gets the Lazy loader factory.
     *
     * @return LazyLoaderFactoryInterface
     */
    public function getLazyLoaderFactory()
    {
        return $this->lazyLoaderFactory;
    }

    /**
     * Sets the Lazy loader factory.
     *
     * @param LazyLoaderFactoryInterface $lazyLoaderFactory the lazy loader factory
     *
     * @return self
     */
    public function setLazyLoaderFactory(LazyLoaderFactoryInterface $lazyLoaderFactory)
    {
        $this->lazyLoaderFactory = $lazyLoaderFactory;

        return $this;
    }

    /**
     * Gets the Logger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets the Logger.
     *
     * @param LoggerInterface $logger the logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    private function __construct() {}

    private function __clone() {}
}
