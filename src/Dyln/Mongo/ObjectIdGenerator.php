<?php

namespace Dyln\Mongo;

use MongoDB\BSON\ObjectID;

class ObjectIdGenerator
{
    public static function createIdFromTimestamp($timestamp)
    {
        return new ObjectID(str_pad(base_convert($timestamp, 10, 16), 24, '0', STR_PAD_RIGHT));
    }
}
