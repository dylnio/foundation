<?php

namespace Dyln\Mongo;

use MongoDB\BSON\ObjectID;

class ObjectIdParser
{
    public static function parse(ObjectID $id)
    {
        $id = (string)$id;
        $timestamp = substr($id, 0, 8);
        $machineId = substr($id, 8, 6);
        $processId = substr($id, 14, 4);
        $counter = substr($id, 18, 6);

        return [
            'timestamp' => hexdec($timestamp),
            'machineid' => hexdec($machineId),
            'processid' => hexdec($processId),
            'counter'   => hexdec($counter),
        ];

    }
}