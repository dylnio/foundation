<?php

namespace Dyln\CommandBus\Command;

use Dyln\Params\Params;

abstract class AbstractCommand implements Command
{
    protected $params = [];

    public function withParams(Params $params)
    {
        $this->params = $params->getAllParams();

        return $this;
    }

    public function withArray($params = [])
    {
        $this->params = $params;

        return $this;
    }

    public function get($key, $default = null)
    {
//        $method = 'get' . ucfirst(strtolower($key));
//        if (method_exists($this, $method)) {
//            return $this->{$method}();
//        }

        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    public function keyExists($key)
    {
        return array_key_exists($key, $this->params);
    }

    public function getAllParams()
    {
        return $this->params;
    }

    public function removeParamPrefix($prefix)
    {
        $params = $this->getAllParams();
        $newParams = [];
        foreach ($params as $key => $value) {
            $newParams[str_replace($prefix, '', $key)] = $value;
        }
        $this->params = $newParams;
    }

    public function set($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->params);
    }

    public function isEmpty($key)
    {
        return $this->has($key) && ($this->get($key) == '' || $this->get($key) == null);
    }
}