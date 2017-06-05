<?php

namespace Dyln\Payload;

use Dyln\Util\ArrayUtil;

class Payload implements PayloadInterface
{
    protected $isError = false;
    protected $messages = [];
    protected $data = [];
    protected $code;

    public function __construct(array $data = [], $isError = false, array $messages = [])
    {
        $this->isError = $isError;
        if (!empty($data)) {
            $this->setData($data);
        }
        if (!empty($messages)) {
            $this->setMessages($messages);
        }
    }

    public function getLastMessage()
    {
        return end($this->messages);
    }

    public function addMessage($key = null, $message)
    {
        if ($key) {
            $this->messages[$key] = $message;
        } else {
            $this->messages[] = $message;
        }

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function isSuccess()
    {
        return !$this->isError();
    }

    public function isError()
    {
        return $this->isError;
    }

    public function error()
    {
        $this->isError = true;

        return $this;
    }

    public function success()
    {
        $this->isError = false;

        return $this;
    }

    public function toArray()
    {
        return [
            'isError'  => $this->isError(),
            'messages' => $this->getMessages(),
            'data'     => $this->getData(),
        ];
    }

    public function getMessages($key = null)
    {
        if ($key) {
            return ArrayUtil::getIn($this->messages, $key);
        }

        return $this->messages;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data = [])
    {
        $this->data = $data;

        return $this;
    }

    public function fromArray($array = [])
    {
        $this->isError = ArrayUtil::getIn($array, 'isError');
        $this->messages = ArrayUtil::getIn($array, 'messages', []);
        $this->data = ArrayUtil::getIn($array, 'data', []);
    }

    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key, $default = null)
    {
        if (strpos($key, '.') !== false) {
            $key = explode('.', $key);
        }

        return ArrayUtil::getIn($this->data, $key, $default);
    }

}