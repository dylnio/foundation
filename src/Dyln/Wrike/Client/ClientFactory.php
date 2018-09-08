<?php

namespace Dyln\Wrike\Client;

class ClientFactory
{
    public static function create($token)
    {
        return new Client($token);
    }
}
