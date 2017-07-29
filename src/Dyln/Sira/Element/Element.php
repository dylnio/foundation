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

    /**
     * Element constructor.
     * @param $id
     * @param $queueName
     * @param $callable
     * @param array $data
     */
    public function __construct($id, $queueName, $callable, $data = [])
    {
        $this->id = $id;
        $this->queueName = $queueName;
        $this->data = $data;
        $this->callable = $callable;
    }


    public function encode()
    {
        return json_encode([
            'id'        => $this->id,
            'data'      => $this->data,
            'queueName' => $this->queueName,
            'callable'  => $this->callable,
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

    static public function fromArray($data)
    {
        return new static($data['id'], $data['queueName'], $data['callable'], $data['data']);
    }
}