<?php

namespace Dyln\Message;

class MessageFactory
{
    static public function success(array $data = []): Message
    {
        $message = new Message();

        return $message->success()->withData($data);
    }

    static public function error(array $error = []): Message
    {
        $message = new Message();

        return $message->error()->withError($error);
    }
}