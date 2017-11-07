<?php

namespace Dyln\Doctrine\Common\Cache;

use Dyln\AppEnv;
use Dyln\Debugbar\Debugbar;
use Dyln\Util\Timer;

class RedisCache extends \Doctrine\Common\Cache\RedisCache
{
    public function fetch($id)
    {
        Timer::start();
        $result = parent::fetch($id);
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'fetch',
                'query'     => json_encode(['id' => $id]),
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function fetchMultiple(array $keys)
    {
        Timer::start();
        $result = parent::fetchMultiple($keys);
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'fetchMultiple',
                'query'     => json_encode(['keys' => $keys]),
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        Timer::start();
        $result = parent::saveMultiple($keysAndValues, $lifetime);
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'saveMultiple',
                'query'     => json_encode(['keysAndValues' => $keysAndValues, 'lifetime' => $lifetime]),
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function contains($id)
    {
        Timer::start();
        $result = parent::contains($id);
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'contains',
                'query'     => json_encode(['id' => $id]),
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function save($id, $data, $lifeTime = 0)
    {
        Timer::start();
        $result = parent::save($id, $data, $lifeTime);
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'doSave',
                'query'     => json_encode(['id' => $id, 'data' => $data, 'lifetime' => $lifeTime]),
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function delete($id)
    {
        Timer::start();
        $result = parent::delete($id);
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'delete',
                'query'     => json_encode(['id' => $id]),
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function flushAll()
    {
        Timer::start();
        $result = parent::flushAll();
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'flushAll',
                'query'     => null,
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }

    public function getStats()
    {
        Timer::start();
        $result = parent::getStats();
        $time = Timer::result();
        if (AppEnv::isDebugEnabled()) {
            $bt = [];
            $traces = debug_backtrace();
            for ($i = 15; $i > 0; $i--) {
                if (isset($traces[$i])) {
                    $t = $traces[$i];
                    $bt[] = [
                        'file'     => isset($t['file']) ? $t['file'] : false,
                        'line'     => isset($t['line']) ? $t['line'] : false,
                        'function' => isset($t['function']) ? $t['function'] : false,
                    ];
                }
            }
            Debugbar::add('Redis', [
                'command'   => 'getStats',
                'query'     => null,
                'time'      => $time,
                'backtrace' => $bt,
                'start'     => Timer::getStart(),
                'end'       => Timer::getEnd(),
            ]);
        }
        return $result;
    }
}
