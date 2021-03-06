<?php

namespace Dyln\Message;

use Dyln\Sentry\ReprInfoProvider;
use Dyln\Util\ArrayUtil;

class Message implements ReprInfoProvider
{
    protected $isError = false;
    protected $data = [];
    protected $errorMessage = null;
    protected $errorCode = null;
    protected $errorExtra = [];

    public function isError()
    {
        return $this->isError;
    }

    public function isSuccess()
    {
        return !$this->isError;
    }

    public function getData()
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        return ArrayUtil::getIn($this->data, $key, $default);
    }

    public function getError()
    {
        if ($this->isError()) {
            return [
                'message' => $this->errorMessage,
                'extra'   => $this->errorExtra,
                'code'    => $this->errorCode,
            ];
        }

        return null;
    }

    public function getErrorMessage()
    {
        if ($this->isError()) {
            return $this->errorMessage;
        }

        return null;
    }

    public function getErrorCode()
    {
        if ($this->isError()) {
            return $this->errorCode;
        }

        return null;
    }

    public function getErrorExtra()
    {
        if ($this->isError()) {
            return $this->errorExtra;
        }

        return [];
    }

    public function setError($message, $code = 0)
    {
        $this->isError = true;
        $this->errorMessage = $message;
        $this->errorCode = $code;

        return $this;
    }

    public function setData($data = [])
    {
        $this->data = $data;

        return $this;
    }

    public function addData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
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

    public function withData(array $data = [])
    {
        $this->data = $data;

        return $this;
    }

    public function withError(array $error = [])
    {
        if (empty($error['message'])) {
            throw new \Exception('Invalid error. Received ' . var_export($error, true));
        }
        $this->errorMessage = $error['message'];
        $this->errorCode = $error['code'] ?? null;
        $this->errorExtra = $error['extra'] ?? null;

        return $this;
    }

    public function toArray()
    {
        return [
            'isError' => $this->isError(),
            'error'   => $this->getError(),
            'data'    => $this->getData(),
        ];
    }

    public function provideReprInfo()
    {
        return [
            'class'   => get_class($this),
            'payload' => $this->toArray(),
            'note'    => 'by \\Dyln\\Sentry\\ReprInfoProvider',
        ];
    }
}
