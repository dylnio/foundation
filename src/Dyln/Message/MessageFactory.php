<?php

namespace Dyln\Message;

class MessageFactory
{
    public static function success(array $data = []): Message
    {
        $message = new Message();

        return $message->success()->withData($data);
    }

    public static function error(array $error = []): Message
    {
        $message = new Message();

        return $message->error()->withError($error);
    }
}
