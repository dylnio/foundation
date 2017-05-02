<?php

namespace Dyln\Session\Handler;

use Dyln\Util\ArrayUtil;
use Dyln\Util\Browser;
use Dyln\Util\IpUtil;
use MongoDB\Database;

class MongoSessionHandler implements \SessionHandlerInterface
{
    /** @var MongoSessionHandler */
    protected static $_instance;
    protected static $memorySession = [];
    /** @var \MongoDB\Collection */
    protected $mongo;
    protected $collectionName;
    protected $doc = [];
    protected $sessionConfig = [];

    /**
     * Instantiate
     *
     * @param Database $db
     * @param string $collection
     * @param array $config for the mongo connection
     *
     * @throws \Exception
     */
    protected function __construct($db, $collection, array $config)
    {
        $this->sessionConfig = ArrayUtil::getIn($config, ['session_config'], []);
        $this->mongo = $db;
        $this->collectionName = $collection;
    }

    /**
     * Gets the current instance
     *
     * @return MongoSessionHandler null if register() has not been called yet
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    public static function register($db, $collection, $config = [])
    {
        if (!self::$_instance) {
            $handler = new self($db, $collection, $config);
            self::$_instance = $handler;
        } else {
            $handler = self::$_instance;
        }

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


    private function getCollection()
    {
        return $this->mongo->selectCollection($this->collectionName);
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
        $result = $this->getCollection()->deleteOne(['_id' => $id], ['w' => 1]);

        return ($result['ok'] == 1);
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
        $this->getCollection()->deleteMany(['expire' => ['$lt' => time()]]);

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
            return self::$memorySession;
        }
        $this->doc = $this->getCollection()->findOne(['_id' => $id]);
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
                'expire' => time() + intval(ini_get('session.gc_maxlifetime')),
                'ip'     => IpUtil::getRealIp(),
                'server' => [
                    'referer'     => ArrayUtil::getIn($_SERVER, ['HTTP_REFERER'], null),
                    'host'        => ArrayUtil::getIn($_SERVER, ['HTTP_HOST'], null),
                    'remote_host' => ArrayUtil::getIn($_SERVER, ['REMOTE_HOST'], null),
                    'agent'       => ArrayUtil::getIn($_SERVER, ['HTTP_USER_AGENT'], null),
                ],
            ];
            if (!isset($this->doc['d']) || (isset($this->doc['d']) && $this->doc['d'] != $data)) {
                $doc['d'] = $data;
            }
            $options = ['upsert' => true];
            $this->getCollection()->updateOne(['_id' => $id], ['$set' => $doc], $options);

            return true;
        }
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        // TODO: Implement close() method.
        return true;
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $name)
    {
        // TODO: Implement open() method.
        return true;
    }
}
