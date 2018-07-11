<?php

namespace Dyln\DI;

use Doctrine\Common\Cache\CacheProvider;
use Dyln\Event\Emitter;
use Dyln\Log\Logger;

class Container extends \DI\Container implements \ArrayAccess
{
    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function get($obj)
    {
        if (!is_object($obj)) {
            $obj = parent::get($obj);
            if (is_string($obj) && class_exists($obj)) {
                $obj = $this->get($obj);
            }
        }

        return $this->decorate($obj);
    }

    public function decorate($obj)
    {
        if ($obj instanceof InitableInterface) {
            $this->call([$obj, 'init']);
        }
        if ($obj instanceof EventEmitterAwareInterface) {
            $obj->setEmitter($this->get(Emitter::class));
        }
        if ($obj instanceof CacheAwareInterface) {
            $obj->setCache($this->get(CacheProvider::class));
        }
        if ($obj instanceof LogAwareInterface) {
            $obj->setLogger($this->get(Logger::class));
        }

        return $obj;
    }
}
