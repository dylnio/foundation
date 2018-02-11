<?php

namespace Dyln\Mongo;

use MongoDB\BSON\ObjectID;

class IsValidObjectId
{
    public static function isValid($id)
    {
        if ($id instanceof ObjectID) {
            return true;
        }

        return preg_match('/^[a-f\d]{24}$/i', $id);
    }
}
