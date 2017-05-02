<?php

namespace Dyln\Mongo;

use MongoDB\BSON\ObjectID;

class IsValidObjectId
{
    static public function isValid($id)
    {
        if ($id instanceof ObjectID) {
            return true;
        }

        return preg_match('/^[a-f\d]{24}$/i', $id);
    }
}