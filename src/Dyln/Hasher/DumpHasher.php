<?php

namespace Dyln\Hasher;

class DumpHasher implements Hasher
{

    public function hash($value)
    {
        return $value;
    }
}