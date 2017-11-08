<?php

namespace Dyln\Doctrine\Common\Cache;

use Dyln\DI\EventEmitterAwareInterface;
use Dyln\Doctrine\Common\Cache\Enum\RedisCacheEvents;
use Dyln\Event\Emitter;

class RedisCache extends \Doctrine\Common\Cache\RedisCache implements EventEmitterAwareInterface
{
    /** @var Emitter */
    protected $emitter;

    public function fetch($id)
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::fetch($id);
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function fetchMultiple(array $keys)
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::fetchMultiple($keys);
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::saveMultiple($keysAndValues, $lifetime);
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function contains($id)
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::contains($id);
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::save($id, $data, $lifeTime);
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function delete($id)
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::delete($id);
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function flushAll()
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::flushAll();
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function getStats()
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => $args,
            'start'   => $start,
        ];
        $this->emit(RedisCacheEvents::BEFORE_COMMAND, $eventParams);
        $result = parent::getStats();
        $end = microtime(true);
        $this->emit(RedisCacheEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $result;
    }

    public function setEmitter(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

    protected function emit($event, $args = [])
    {
        if ($this->emitter) {
            $this->emitter->emit($event, $args);
        }
    }
}
