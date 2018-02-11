<?php

namespace Dyln\Sira\Element;

class Element
{
    protected $id;
    protected $data = [];
    protected $queueName;
    protected $callable;
    protected $startTime;
    protected $endTime;
    protected $error;
    protected $try = 0;
    protected $maxTry = 1;

    /**
     * Element constructor.
     * @param $id
     * @param $queueName
     * @param $callable
     * @param array $data
     * @param null $error
     * @param int $try
     * @param int $maxTry
     */
    public function __construct($id, $queueName, $callable, $data = [], $error = null, $try = 0, $maxTry = 1)
    {
        $this->id = $id;
        $this->queueName = $queueName;
        $this->data = $data;
        $this->callable = $callable;
        $this->error = $error;
        $this->try = $try;
        $this->maxTry = $maxTry;
    }

    public function encode()
    {
        return json_encode([
            'id'        => $this->id,
            'data'      => $this->data,
            'queueName' => $this->queueName,
            'callable'  => $this->callable,
            'error'     => $this->error,
            'try'       => $this->try,
            'maxTry'    => $this->maxTry,
        ], true);
    }

    public function getQueueName()
    {
        return $this->queueName;
    }

    public function getId()
    {
        return $this->id;
    }

    public function start()
    {
        $this->startTime = time();
    }

    public function end()
    {
        $this->endTime = time();
    }

    public static function fromArray($data)
    {
        return new static($data['id'], $data['queueName'], $data['callable'], $data['data'], $data['error'], $data['try'], $data['maxTry']);
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setError($error)
    {
        return $this->error[$this->try] = $error;
    }

    public function incTry()
    {
        $this->try++;
    }

    public function getTryCount()
    {
        return $this->try;
    }

    public function getMaxTry()
    {
        return $this->maxTry;
    }

    public function canRetry()
    {
        return $this->try < $this->maxTry;
    }

    public function isError()
    {
        return isset($this->error[$this->try]);
    }
}
