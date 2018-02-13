<?php

namespace Dyln\Slim\Http;

class Stream extends \Slim\Http\Stream
{
    protected function attach($stream)
    {
        if (is_string($stream)) {
            $stream = fopen($stream, '+r');
        }
        parent::attach($stream);
    }
}
