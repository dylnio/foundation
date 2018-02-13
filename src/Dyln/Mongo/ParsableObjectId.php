<?php

namespace Dyln\Mongo;

use MongoDB\BSON\ObjectID;

class ParsableObjectId
{
    /** @var  ObjectID */
    protected $id;
    protected $timestamp;
    protected $machineId;
    protected $processId;
    protected $counter;

    /**
     * ObjectIdParser constructor.
     * @param ObjectID $id
     */
    public function __construct(ObjectID $id = null)
    {
        $this->id = $id ?: new ObjectID();
        $this->parse();
    }

    private function parse()
    {
        $id = (string)$this->id;
        $this->timestamp = hexdec(substr($id, 0, 8));
        $this->machineId = hexdec(substr($id, 8, 6));
        $this->processId = hexdec(substr($id, 14, 4));
        $this->counter = hexdec(substr($id, 18, 6));
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return mixed
     */
    public function getMachineId()
    {
        return $this->machineId;
    }

    /**
     * @return mixed
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * @return mixed
     */
    public function getCounter()
    {
        return $this->counter;
    }
}
