<?php

namespace Dyln\Session\Handler;

use Dyln\Mongo\MongofyKeys;
use Dyln\Util\ArrayUtil;
use Dyln\Util\Browser;
use MongoDB\Database;
use MongoDB\Driver\Manager;
use function Dyln\getin;

class MongoSessionHandler
{
    /** @var MongoSessionHandler */
    protected static $_instance;
    protected static $memorySession = [];
    /** @var \MongoDB\Collection */
    protected $sessionCollection;
    /** @var \MongoDB\Collection */
    protected $sessionDataCollection;
    protected $sessionConfig = [];
    protected $session = [];
    protected $sessionData = [];

    protected function __construct($host, $databaseName, $collectionName, array $config)
    {
        $this->sessionConfig = ArrayUtil::getIn($config, ['session_config'], []);
        $manager = new Manager($host, ArrayUtil::getIn($this->sessionConfig, ['handler', 'mongo', 'uri_options'], []), ArrayUtil::getIn($this->sessionConfig, ['handler', 'mongo', 'driver_options'], []));
        $db = new Database($manager, $databaseName, [
            'typeMap' => [
                'array'    => 'array',
                'document' => 'array',
                'root'     => 'array',
            ],
        ]);
        $this->sessionCollection = $db->selectCollection($collectionName);
        $this->sessionDataCollection = $db->selectCollection($collectionName . '_data');
        $serMethod = ini_get("session.serialize_handler");
        if ($serMethod != 'igbinary') {
            if (extension_loaded('igbinary')) {
                ini_set('session.serialize_handler', 'igbinary');
            } else {
                ini_set('session.serialize_handler', 'php_serialize');
            }
        }
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

    public static function register($host, $databaseName, $collectionName, array $config = [])
    {
        if (!self::$_instance) {
            $handler = new self($host, $databaseName, $collectionName, $config);
            self::$_instance = $handler;
        } else {
            $handler = self::$_instance;
        }
        $res = session_set_save_handler(
            [$handler, "open"],
            [$handler, "close"],
            [$handler, "read"],
            [$handler, "write"],
            [$handler, "destroy"],
            [$handler, "gc"]
        );

        return $res;
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
        $result = $this->sessionCollection->deleteOne(['_id' => $id], ['w' => 1]);
        $this->sessionDataCollection->deleteOne(['_id' => $id], ['w' => 1]);
        $this->session = [];
        $this->sessionData = [];

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
        $this->sessionCollection->deleteMany(['expire' => ['$lt' => time()]]);
        $this->sessionDataCollection->deleteMany(['expire' => ['$lt' => time()]]);

        return true;
    }


    /**
     * A no-op function, somethings just aren't worth doing.
     */
    public function noop()
    {
        return true;
    }

    public function read($id)
    {
        if ($this->isIgnorableSession()) {
            return self::$memorySession;
        }
        $session = $this->sessionCollection->findOne(['_id' => $id]);
        if (!$session) {
            return false;
        }
        $this->session = $session;
        $sessionData = $this->sessionDataCollection->findOne(['_id' => $id]);
        if (!$sessionData) {
            $this->sessionCollection->deleteOne(['_id' => $id]);

            return false;
        }
        $this->sessionData = $sessionData;
        $data = getin($sessionData, ['data']);

        return $data ? $this->serialize(MongofyKeys::unsafe($data)) : false;
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
            $options = ['upsert' => true];
            $data = $this->unserialize($data);
            $existingHash = md5(json_encode($this->sessionData['data'] ?? []));
            $newHash = md5(json_encode($data));
            $sessionData = [
                'expire' => time() + intval(ini_get('session.gc_maxlifetime')),
            ];
//            if ($existingHash != $newHash) {
            $sessionData['data'] = MongofyKeys::safe($data);
//            }
            $this->sessionDataCollection->updateOne(['_id' => $id], ['$set' => $sessionData], $options);
            $session = [
                'expire' => time() + intval(ini_get('session.gc_maxlifetime')),
                'hash'   => $newHash,
            ];
            $this->sessionCollection->updateOne(['_id' => $id], ['$set' => $session], $options);

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
        return true;
    }

    private function unserialize($session_data)
    {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php_serialize":
                return unserialize($session_data);
            case "igbinary":
                return igbinary_unserialize($session_data);
            default:
                throw new \Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php_serialize, igbinary");
        }
    }

    private function serialize($session_data)
    {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php_serialize":
                return serialize($session_data);
            case "igbinary":
                return igbinary_serialize($session_data);
            default:
                throw new \Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php_serialize, igbinary");
        }
    }
}
