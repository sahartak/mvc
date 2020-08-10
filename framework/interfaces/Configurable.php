<?php
namespace app\framework\interfaces;

interface Configurable
{
    /**
     * Set class configs
     * @param array $configs
     * @return bool
     */
    public static function setConfigs(array $configs): bool;
    
    /**
     * Validate class configs
     * @return bool
     */
    public static function validateConfigs(): bool;
}