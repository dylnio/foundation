<?php

namespace Dyln\Hasher;

class Sha1HasherWithSalt implements Hasher
{
    protected $salt;
    protected $prepend = false;

    public function __construct($salt, $prepend = false)
    {
        $this->salt = $salt;
        $this->prepend = $prepend;
    }

    public function hash($value)
    {
        $value = $this->prepend ? $this->salt . $value : $value . $this->salt;

        return sha1($value);
    }
}