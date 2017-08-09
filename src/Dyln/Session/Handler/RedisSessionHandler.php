<?php

namespace Dyln\Session\Handler;

use Dyln\Util\ArrayUtil;
use Dyln\Util\Browser;
use Dyln\Util\IpUtil;

class RedisSessionHandler implements \SessionHandlerInterface
{
    /** @var RedisSessionHandler */
    protected static $_instance;
    protected static $memorySession = [];
    protected $host;
    protected $port;
    protected $db;
    /** @var \Redis */
    protected $redis;
    protected $doc = [];
    protected $sessionConfig = [];

    /**
     * Instantiate
     *
     * @param $host
     * @param $port
     * @param int $db
     * @param array $config for the mongo connection
     *
     * @internal param \Redis $redis
     */
    protected function __construct($host, $port, $db = 9, array $config)
    {
        $this->sessionConfig = ArrayUtil::getIn($config, ['session_config'], []);
        $this->host = $host;
        $this->port = $port;
        $this->db = $db;
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
        $this->redis->setOption(\Redis::OPT_SERIALIZER, defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP);
        $this->redis->select($db);
    }

    /**
     * Gets the current instance
     *
     * @return RedisSessionHandler null if register() has not been called yet
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    /**
     * Registers the handler into PHP
     *
     * @param $host
     * @param $port
     * @param int|string $db
     * @param array $config
     * @return bool
     */
    public static function register($host, $port, $db = 9, array $config = [])
    {
        if (!self::$_instance) {
            $handler = new self($host, $port, $db, $config);
            self::$_instance = $handler;
        } else {
            $handler = self::$_instance;
        }

        // boom.
        return session_set_save_handler(
            [
                $handler,
                'open',
            ],
            [
                $handler,
                'close',
            ],
            [
                $handler,
                'read',
            ],
            [
                $handler,
                'write',
            ],
            [
                $handler,
                'destroy',
            ],
            [
                $handler,
                'gc',
            ]
        );
    }

    public function open($save_path, $session_id)
    {
        if (!$this->redis) {
            $this->redis = new \Redis();
            $this->redis->connect($this->host, $this->port);
            $this->redis->setOption(\Redis::OPT_SERIALIZER, defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP);
            $this->redis->select($this->db);
        }

        return true;
    }

    public function close()
    {
        $this->redis = null;

        return true;
    }

    /**
     * Destroy's the session
     *
     * @param string $id
     *
     * @return bool
     */
    public function destroy($id)
    {
        if ($this->isIgnorableSession()) {
            return true;
        }
        $this->redis->del($id);

        return true;
    }

    private function isIgnorableSession()
    {
        if (PHP_SAPI == "cli") {
            return true;
        }
        $userAgent = Browser::getUserAgent();
        $bots = ArrayUtil::getIn($this->sessionConfig, ['bots'], []);
        $bots[] = 'eBayNioHttpClient';
        foreach ($bots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Triggers the garbage collector, we do this with a mongo
     * safe=false delete, as that will return immediately without
     * blocking php.
     *
     * Maybe it'll delete stuff, maybe it won't. The next GC
     * will get'em.... eventually :)
     *
     * @param $max
     *
     * @return bool
     */
    public function gc($max)
    {
        return true;
    }


    /**
     * A no-op function, somethings just aren't worth doing.
     */
    public function noop()
    {
        return true;
    }

    /**
     * Reads the session from Mongo
     *
     * @param string $id
     *
     * @return string
     */
    public function read($id)
    {
        if ($this->isIgnorableSession()) {
            return self::$memorySession[$id] ?? null;
        }
        $this->doc = $this->redis->get($id);
        if (!isset($this->doc['d'])) {
            return false;
        } else {
            if (strpos($this->doc['d'], "__ENCODED__") === 0) {
                $data = substr($this->doc['d'], 11);
                $data = base64_decode($data);

                return $data;
            } else {
                return $this->doc['d'];
            }
        }
    }

    /**
     * Writes the session data back to mongo
     *
     * @param string $id
     * @param string $data
     *
     * @return bool
     */
    public function write($id, $data)
    {
        if ($this->isIgnorableSession()) {
            self::$memorySession = $data;

            return true;
        }
        if (empty($data)) {
            return true;
        } else {
            $doc = [
                'ip'     => IpUtil::getRealIp(),
                'server' => [
                    'referer'     => ArrayUtil::getIn($_SERVER, ['HTTP_REFERER'], null),
                    'host'        => ArrayUtil::getIn($_SERVER, ['HTTP_HOST'], null),
                    'remote_host' => ArrayUtil::getIn($_SERVER, ['REMOTE_HOST'], null),
                    'agent'       => ArrayUtil::getIn($_SERVER, ['HTTP_USER_AGENT'], null),
                ],
            ];
            $doc['d'] = $data;
            $timeout = (int)ini_get('session.gc_maxlifetime');
            $this->redis->set($id, $doc, $timeout);

            return true;
        }
    }
}
