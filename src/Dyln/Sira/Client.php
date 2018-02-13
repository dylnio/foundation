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
        $length = $this->getRedis()->rpush('queue:' . $element->getQueueName() . ':waiting', $encodedItem);
        if ($length < 1) {
            return false;
        }

        return true;
    }

    public function fail(Element $element)
    {
        $encodedItem = $element->encode();
        if ($encodedItem === false) {
            return false;
        }
        $length = $this->getRedis()->rpush('queue:' . $element->getQueueName() . ':failed', $encodedItem);
        if ($length < 1) {
            return false;
        }

        return true;
    }

    public function success(Element $element)
    {
        $encodedItem = $element->encode();
        if ($encodedItem === false) {
            return false;
        }
        $length = $this->getRedis()->rpush('queue:' . $element->getQueueName() . ':success', $encodedItem);
        if ($length < 1) {
            return false;
        }

        return true;
    }

    public function remove(Element $element)
    {
        $counter = 0;
        $originalQueue = 'queue:' . $element->getQueueName() . ':waiting';
        $tempQueue = 'queue:' . $element->getQueueName() . ':temp';
        $requeueQueue = 'queue:' . $element->getQueueName() . ':requeue';
        $finished = false;
        while (!$finished) {
            $string = $this->getRedis()->rpoplpush($originalQueue, $tempQueue);
            if (!empty($string)) {
                if ($this->matchItem($string, $element)) {
                    $this->getRedis()->rpop($tempQueue);
                    $counter++;
                } else {
                    $this->getRedis()->rpoplpush($tempQueue, $requeueQueue);
                }
            } else {
                $finished = true;
            }
        }
        $finished = false;
        while (!$finished) {
            $string = $this->getRedis()->rpoplpush($requeueQueue, $originalQueue);
            if (empty($string)) {
                $finished = true;
            }
        }
        // remove temp queue and requeue queue
        $this->getRedis()->del($requeueQueue);
        $this->getRedis()->del($tempQueue);

        return $counter;
    }

    public function requeueFailed(Element $element)
    {
        $originalQueue = 'queue:' . $element->getQueueName() . ':failed';
        $tempQueue = 'queue:' . $element->getQueueName() . ':temp_failed';
        $requeueQueue = 'queue:' . $element->getQueueName() . ':requeue_failed';
        $finished = false;
        while (!$finished) {
            $string = $this->getRedis()->rpoplpush($originalQueue, $tempQueue);
            if (!empty($string)) {
                if ($this->matchItem($string, $element)) {
                    $this->push($element);
                    $finished = true;
                } else {
                    $this->getRedis()->rpoplpush($tempQueue, $requeueQueue);
                }
            }
        }
        $finished = false;
        while (!$finished) {
            $string = $this->getRedis()->rpoplpush($requeueQueue, $originalQueue);
            if (empty($string)) {
                $finished = true;
            }
        }
        // remove temp queue and requeue queue
        $this->getRedis()->del($requeueQueue);
        $this->getRedis()->del($tempQueue);
    }

    private function matchItem($string, Element $element)
    {
        $decoded = json_decode($string, true);
        $readElement = Element::fromArray($decoded);

        return $element->getId() == $readElement->getId();
    }

    /**
     * @param $queue
     * @return null|Element
     */
    public function pop($queue)
    {
        $item = $this->getRedis()->lPop('queue:' . $queue . ':waiting');
        if (!$item) {
            return null;
        }
        $item = json_decode($item, true);
        $element = Element::fromArray($item);
        $element->incTry();
        $element->start();

        return $element;
    }

    public function getRedis()
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
