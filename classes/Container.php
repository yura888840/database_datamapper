<?php

namespace Migration;

/**
 * Class Container Simple implementation of IoC Container
 * @package Migration
 */
class Container
{
    private $container = [];

    public function set($serviceId, $serviceDefinition)
    {
        if (!array_key_exists($serviceId, $this->container)) {
            $this->container[$serviceId] = $serviceDefinition;
        }
    }

    public function get($serviceId, $params = NULL)
    {
        if (array_key_exists($serviceId, $this->container)) {
            if (!empty($params) && is_callable($this->container[$serviceId])) {
                return call_user_func_array($this->container[$serviceId], $params);
            } elseif (is_callable($this->container[$serviceId])) {
                return $this->container[$serviceId] ();
            } elseif (is_object($this->container[$serviceId])) {
                return $this->container[$serviceId];
            } else {
                return $this->container[$serviceId];
            }
        }

        return null;
    }
}
