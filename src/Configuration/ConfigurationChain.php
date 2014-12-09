<?php

namespace Kassko\DataMapper\Configuration;

use SplStack;
use RuntimeException;

/**
 * Configuration really used and which responds in the name of runtime's configurations and the original configuration.
 *
 * @author kko
 */
class ConfigurationChain extends Configuration
{
    private $runtimeConfigurations;

    public function __construct()
    {
        $this->runtimeConfigurations = new SplStack;
    }

    public function pushRuntimeConfiguration(RuntimeConfiguration $runtimeConfiguration)
    {
        $runtimeConfiguration->setDefaultClassMetadataResourceType($this->defaultClassMetadataResourceType);
        $runtimeConfiguration->setDefaultClassMetadataResourceDir($this->defaultClassMetadataResourceDir);
        $this->runtimeConfigurations->push($runtimeConfiguration);

        return $this;
    }

    public function popRuntimeConfiguration()
    {
        $this->runtimeConfigurations->pop();
        return $this;
    }

    /**
     * Keep only the original configuration.
     */
    public function resetConfiguration()
    {
        $this->runtimeConfigurations = new SplStack;
        return $this;
    }

    public function getClassMetadataCacheConfig()
    {
        foreach ($this->runtimeConfigurations as $runtimeConfiguration) {

            if (! empty($value = $runtimeConfiguration->getClassMetadataCacheConfig())) {
                return $value;
            }
        }

        return parent::getClassMetadataCacheConfig();
    }

    public function getResultCacheConfig()
    {
        foreach ($this->runtimeConfigurations as $runtimeConfiguration) {

            if (! empty($value = $runtimeConfiguration->getResultCacheConfig())) {
                return $value;
            }
        }

        return parent::getResultCacheConfig();
    }

    public function getClassMetadataResource($objectName)
    {
        foreach ($this->runtimeConfigurations as $runtimeConfiguration) {

            if (! empty($value = $runtimeConfiguration->getClassMetadataResource($objectName))) {
                return $value;
            }
        }

        return parent::getClassMetadataResource($objectName);
    }

    public function getClassMetadataResourceType($objectName)
    {
        foreach ($this->runtimeConfigurations as $runtimeConfiguration) {

            if (! empty($value = $runtimeConfiguration->getClassMetadataResourceType($objectName))) {
                return $value;
            }
        }

        return parent::getClassMetadataResourceType($objectName);
    }

    public function getClassMetadataDir($objectName)
    {
        foreach ($this->runtimeConfigurations as $runtimeConfiguration) {

            if (! empty($value = $runtimeConfiguration->getClassMetadataDir($objectName))) {
                return $value;
            }
        }

        return parent::getClassMetadataDir($objectName);
    }
}
