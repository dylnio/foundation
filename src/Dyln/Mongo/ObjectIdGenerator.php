<?php

namespace Dyln\Mongo;

use MongoDB\BSON\ObjectID;

class ObjectIdGenerator
{
    public static function createIdFromTimestamp($timestamp)
    {
        static $inc = 0;

        $ts = pack('N', $timestamp);
        $m = substr(md5(gethostname()), 0, 3);
        $pid = pack('n', posix_getpid());
        $trail = substr(pack('N', $inc++), 1, 3);

        $bin = sprintf("%s%s%s%s", $ts, $m, $pid, $trail);

        $id = '';
        for ($i = 0; $i < 12; $i++) {
            $id .= sprintf("%02X", ord($bin[$i]));
        }

        return new ObjectID($id);
    }
}