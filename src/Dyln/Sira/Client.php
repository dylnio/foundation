<?php

namespace Dyln\Sira;

use Dyln\Sira\Element\Element;

class Client
{
    /** @var  string */
    protected $redisHost;
    /** @var  integer */
    protected $redisPort;
    /** @var  integer */
    protected $redisDb;
    /** @var  string */
    protected $prefix;
    /** @var  \Redis */
    protected $redis;

    /**
     * Client constructor.
     * @param string $redisHost
     * @param int $redisPort
     * @param int $redisDb
     * @param string $prefix
     */
    public function __construct($redisHost, $redisPort, $redisDb, $prefix)
    {
        $this->redisHost = $redisHost;
        $this->redisPort = $redisPort;
        $this->redisDb = $redisDb;
        $this->prefix = $prefix;
    }

    public function push(Element $element)
    {
        $encodedItem = $element->encode();
        if ($encodedItem === false) {
            return false;
        }
        $this->getRedis()->sAdd('queues', $element->getQueueName());
        $length = $this->redis->rpush('queue:' . $element->getQueueName() . ':' . $element->getId(), $encodedItem);
        if ($length < 1) {
            return false;
        }

        return true;
    }

    public function pop($queue)
    {
        $item = $this->getRedis()->lPop('queue:' . $queue);
        if (!$item) {
            return false;
        }
        $item = json_decode($item, true);

        return Element::fromArray($item);
    }

    private function getRedis()
    {
        if (!$this->redis) {
            $this->redis = new \Redis();
            $this->redis->connect($this->redisHost, $this->redisPort);
            $this->redis->setOption(\Redis::OPT_SERIALIZER, defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP);
            $this->redis->select($this->redisDb);
        }

        return $this->redis;
    }

}