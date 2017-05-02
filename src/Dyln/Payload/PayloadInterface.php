<?php

namespace Dyln\Payload;

interface PayloadInterface
{
    public function getData();

    public function getMessages();

    public function isError();

    public function isSuccess();

    public function setMessages(array $messages);

    public function addMessage($key = null, $message);

    public function error();

    public function success();

    public function setData($data = []);
}